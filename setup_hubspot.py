#!/usr/bin/env python3
"""
ShadowBridge HubSpot Setup
Create custom properties, pipeline, and automations
"""

import requests
import json
import os
import sys

def setup_hubspot(api_key):
    """Setup HubSpot CRM for ShadowBridge"""

    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }

    base_url = 'https://api.hubapi.com'

    print("=" * 60)
    print("ShadowBridge HubSpot Setup")
    print("=" * 60)

    # 1. Create custom properties (Contacts)
    print("\n[1/4] Creating custom properties (Contacts)...")
    contact_properties = [
        {
            'name': 'shadowbridge_inquiry_message',
            'label': 'ShadowBridge Inquiry Message',
            'type': 'string',
            'fieldType': 'textarea',
            'groupName': 'contactinformation'
        },
        {
            'name': 'shadowbridge_contact_source',
            'label': 'ShadowBridge Contact Source',
            'type': 'enumeration',
            'fieldType': 'select',
            'groupName': 'contactinformation',
            'options': [
                {'label': 'Contact Form', 'value': 'contact_form'},
                {'label': 'Email', 'value': 'email'},
                {'label': 'API', 'value': 'api'},
                {'label': 'IMAP Sync', 'value': 'imap_sync'}
            ]
        }
    ]

    for prop in contact_properties:
        response = requests.post(
            f'{base_url}/crm/v3/properties/contacts',
            headers=headers,
            json=prop,
            timeout=10
        )
        if response.status_code in [200, 201]:
            print(f"  ✓ {prop['name']}")
        elif response.status_code == 409:
            print(f"  ~ {prop['name']} (already exists)")
        else:
            print(f"  ✗ {prop['name']}: {response.status_code}")
            print(f"    {response.text}")

    # 2. Create custom properties (Deals)
    print("\n[2/4] Creating custom properties (Deals)...")
    deal_properties = [
        {
            'name': 'shadowbridge_inquiry_type',
            'label': 'ShadowBridge Inquiry Type',
            'type': 'string',
            'fieldType': 'text',
            'groupName': 'dealinformation'
        },
        {
            'name': 'shadowbridge_budget_range',
            'label': 'ShadowBridge Budget Range',
            'type': 'enumeration',
            'fieldType': 'select',
            'groupName': 'dealinformation',
            'options': [
                {'label': '< €10,000', 'value': 'under_10k'},
                {'label': '€10,000 - €50,000', 'value': '10k_50k'},
                {'label': '€50,000+', 'value': 'over_50k'},
                {'label': 'Not Specified', 'value': 'not_specified'}
            ]
        }
    ]

    for prop in deal_properties:
        response = requests.post(
            f'{base_url}/crm/v3/properties/deals',
            headers=headers,
            json=prop,
            timeout=10
        )
        if response.status_code in [200, 201]:
            print(f"  ✓ {prop['name']}")
        elif response.status_code == 409:
            print(f"  ~ {prop['name']} (already exists)")
        else:
            print(f"  ✗ {prop['name']}: {response.status_code}")

    # 3. Create pipeline
    print("\n[3/4] Creating pipeline...")
    pipeline_data = {
        'label': 'ShadowBridge Sales',
        'displayOrder': 0,
        'stages': [
            {'label': 'New Lead', 'displayOrder': 0, 'probability': 0.1, 'metadata': {'isClosed': False}},
            {'label': 'Engaged', 'displayOrder': 1, 'probability': 0.3, 'metadata': {'isClosed': False}},
            {'label': 'Proposal Sent', 'displayOrder': 2, 'probability': 0.6, 'metadata': {'isClosed': False}},
            {'label': 'Negotiation', 'displayOrder': 3, 'probability': 0.8, 'metadata': {'isClosed': False}},
            {'label': 'Closed Won', 'displayOrder': 4, 'probability': 1.0, 'metadata': {'isClosed': True}},
            {'label': 'Closed Lost', 'displayOrder': 5, 'probability': 0.0, 'metadata': {'isClosed': True}}
        ]
    }

    response = requests.post(
        f'{base_url}/crm/v3/pipelines/deals',
        headers=headers,
        json=pipeline_data,
        timeout=10
    )

    if response.status_code in [200, 201]:
        pipeline_id = response.json().get('id')
        print(f"  ✓ Pipeline created: {pipeline_id}")
    elif response.status_code == 409:
        print(f"  ~ Pipeline already exists")
    else:
        print(f"  ✗ Pipeline creation failed: {response.status_code}")
        print(f"    {response.text}")

    # 4. Test contact creation
    print("\n[4/4] Testing contact creation...")
    test_contact = {
        'properties': {
            'email': 'test@shadowbridge.store',
            'firstname': 'ShadowBridge',
            'lastname': 'Test',
            'phone': '+421-555-0123',
            'company': 'ShadowBridge Test',
            'hs_lead_status': 'NEW',
            'shadowbridge_contact_source': 'contact_form'
        }
    }

    response = requests.post(
        f'{base_url}/crm/v3/objects/contacts',
        headers=headers,
        json=test_contact,
        timeout=10
    )

    if response.status_code in [200, 201]:
        contact = response.json()
        print(f"  ✓ Test contact created: {contact['id']}")
        print(f"    Email: {contact['properties']['email']}")
    else:
        print(f"  ✗ Test contact failed: {response.status_code}")
        print(f"    {response.text}")

    print("\n" + "=" * 60)
    print("Setup Complete!")
    print("=" * 60)

if __name__ == '__main__':
    api_key = os.getenv('HUBSPOT_API_KEY') or (sys.argv[1] if len(sys.argv) > 1 else None)

    if not api_key:
        print("Usage: python3 setup_hubspot.py <API_KEY>")
        print("Or set HUBSPOT_API_KEY environment variable")
        sys.exit(1)

    setup_hubspot(api_key)
