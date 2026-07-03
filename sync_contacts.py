#!/usr/bin/env python3
"""
ShadowBridge.Store Contact Sync
Zoho Mail (IMAP) → HubSpot Contacts
Runs every 15 minutes via cron
"""

import imaplib
import json
import requests
import os
import sys
import sqlite3
import logging
from datetime import datetime
from email.parser import Parser
from email.utils import parsedate_to_datetime
from collections import defaultdict
from pathlib import Path

# Setup logging
log_path = os.getenv('LOG_PATH', '/tmp/shadowbridge_hubspot.log')
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(log_path),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

# Config from .env
env_file = os.path.dirname(__file__) + '/.env'
if not os.path.exists(env_file):
    logger.error('.env not found')
    sys.exit(1)

env_config = {}
with open(env_file, 'r') as f:
    for line in f:
        line = line.strip()
        if line and not line.startswith('#') and '=' in line:
            k, v = line.split('=', 1)
            env_config[k.strip()] = v.strip()

HUBSPOT_KEY = env_config.get('HUBSPOT_API_KEY')
ZOHO_HOST = env_config.get('ZOHO_MAIL_HOST')
ZOHO_PORT = int(env_config.get('ZOHO_MAIL_PORT', 993))
ZOHO_ACCOUNTS_STR = env_config.get('ZOHO_MAIL_ACCOUNTS', '')
DB_PATH = env_config.get('DB_PATH', '/var/lib/shadowbridge/hubspot_sync.db')

if not HUBSPOT_KEY or not ZOHO_HOST or not ZOHO_ACCOUNTS_STR:
    logger.error('Missing required .env variables')
    sys.exit(1)

# Parse accounts: email:password,email:password
ZOHO_ACCOUNTS = []
for account in ZOHO_ACCOUNTS_STR.split(','):
    if ':' in account:
        email, password = account.split(':', 1)
        ZOHO_ACCOUNTS.append({'email': email.strip(), 'password': password.strip()})

logger.info(f'Loaded {len(ZOHO_ACCOUNTS)} Zoho Mail accounts')

# Setup SQLite for dedup
os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
db = sqlite3.connect(DB_PATH)
db.execute('''CREATE TABLE IF NOT EXISTS synced_contacts (
    email TEXT PRIMARY KEY,
    contact_id TEXT,
    last_synced TIMESTAMP,
    message_ids TEXT
)''')
db.commit()

def extract_contacts_from_imap(email, password):
    """Extract sender/recipient emails from IMAP mailbox"""
    contacts = defaultdict(lambda: {'emails': [], 'first_seen': None, 'last_seen': None})
    msg_count = 0

    try:
        logger.info(f'Connecting to {ZOHO_HOST} as {email}...')
        mail = imaplib.IMAP4_SSL(ZOHO_HOST, ZOHO_PORT)
        mail.login(email, password)
        mail.select('INBOX')

        # Fetch last 500 messages
        status, messages = mail.search(None, 'ALL')
        msg_ids = messages[0].split()[-500:]

        logger.info(f'Processing {len(msg_ids)} messages from {email}')
        parser = Parser()

        for msg_id in msg_ids:
            try:
                status, msg_data = mail.fetch(msg_id, '(RFC822)')
                if not msg_data[0]:
                    continue

                msg_str = msg_data[0][1].decode('utf-8', errors='ignore')
                msg = parser.parsestr(msg_str)
                msg_count += 1

                # Extract From
                from_addr = msg.get('From', '')
                if '<' in from_addr and '>' in from_addr:
                    from_addr = from_addr.split('<')[1].split('>')[0]
                from_addr = from_addr.strip().lower()

                # Extract To
                to_addrs = []
                for to_field in [msg.get('To', ''), msg.get('Cc', '')]:
                    if to_field:
                        for addr in to_field.split(','):
                            if '<' in addr and '>' in addr:
                                addr = addr.split('<')[1].split('>')[0]
                            to_addrs.append(addr.strip().lower())

                # Parse date
                try:
                    msg_date = msg.get('Date')
                    if msg_date:
                        msg_datetime = parsedate_to_datetime(msg_date)
                    else:
                        msg_datetime = datetime.now()
                except:
                    msg_datetime = datetime.now()

                subject = msg.get('Subject', '')[:100]

                # Add contacts (filter internal)
                for addr in [from_addr] + to_addrs:
                    if not addr or '@' not in addr:
                        continue
                    if 'shadowbridge.store' in addr or 'localhost' in addr:
                        continue

                    if addr not in contacts:
                        contacts[addr]['first_seen'] = msg_datetime

                    contacts[addr]['last_seen'] = msg_datetime
                    contacts[addr]['emails'].append({'subject': subject, 'date': msg_datetime})

            except Exception as e:
                logger.warning(f'Error parsing message {msg_id}: {e}')
                continue

        mail.close()
        mail.logout()
        logger.info(f'Extracted {len(contacts)} unique contacts from {email} ({msg_count} messages)')

    except Exception as e:
        logger.error(f'IMAP error for {email}: {e}')
        return {}

    return contacts

def sync_contact_to_hubspot(email, data):
    """Create/update contact in HubSpot"""
    try:
        payload = {
            'properties': {
                'email': email,
                'source': 'shadowbridge_imap_sync',
                'hs_lead_status': 'SUBSCRIBER',
                'last_contact_activity': data['last_seen'].isoformat() if data['last_seen'] else '',
                'contact_method': 'shadowbridge.store_email',
                'contact_first_seen': data['first_seen'].isoformat() if data['first_seen'] else ''
            }
        }

        response = requests.post(
            'https://api.hubapi.com/crm/v3/objects/contacts',
            headers={'Authorization': f'Bearer {HUBSPOT_KEY}'},
            json=payload,
            timeout=10
        )

        if response.status_code in [200, 201]:
            contact_id = response.json().get('id')

            # Update DB
            db.execute(
                'INSERT OR REPLACE INTO synced_contacts (email, contact_id, last_synced, message_ids) VALUES (?, ?, ?, ?)',
                (email, contact_id, datetime.now().isoformat(), json.dumps([e['subject'] for e in data['emails'][-5:]]))
            )
            db.commit()

            logger.info(f'✓ Synced {email} (ID: {contact_id})')
            return True
        else:
            logger.warning(f'✗ HubSpot sync failed for {email}: {response.status_code} - {response.text}')
            return False

    except Exception as e:
        logger.error(f'Sync error for {email}: {e}')
        return False

def main():
    logger.info('=== ShadowBridge Contact Sync Start ===')

    all_contacts = {}
    total_synced = 0

    for account in ZOHO_ACCOUNTS:
        try:
            contacts = extract_contacts_from_imap(account['email'], account['password'])
            all_contacts.update(contacts)
        except Exception as e:
            logger.error(f'Failed to process {account["email"]}: {e}')
            continue

    logger.info(f'Total unique contacts: {len(all_contacts)}')

    # Check for existing contacts in DB
    existing = set()
    for row in db.execute('SELECT email FROM synced_contacts').fetchall():
        existing.add(row[0])

    new_count = 0
    for email, data in all_contacts.items():
        if email not in existing:
            if sync_contact_to_hubspot(email, data):
                total_synced += 1
                new_count += 1

    logger.info(f'Synced {total_synced} contacts ({new_count} new)')
    logger.info('=== ShadowBridge Contact Sync Complete ===')

    db.close()

if __name__ == '__main__':
    main()
