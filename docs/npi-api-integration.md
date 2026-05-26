# NPI Lookup API Integration – Technical Implementation Details

## Objective

Implement NPI Number lookup functionality using the official CMS NPI Registry API.

Users should be able to search providers using:

- First Name
- Last Name (Required)
- City
- State (Required)
- ZIP Code

The system should return matching provider records with NPI information.

---

# Official API Reference

- CMS NPI Registry API Documentation  
  https://npiregistry.cms.hhs.gov/api-page

- NPI Registry Search Portal  
  https://npiregistry.cms.hhs.gov/

---

# API Endpoint

```http
GET https://npiregistry.cms.hhs.gov/api/
```

---

# Required Static Parameters

| Parameter | Value |
|---|---|
| version | 2.1 |
| limit | 10 |

---

# Form Fields Mapping

| UI Field | API Parameter | Required |
|---|---|---|
| First Name | first_name | No |
| Last Name | last_name | Yes |
| City | city | No |
| State | state | Yes |
| ZIP Code | postal_code | No |

---

# Validation Rules

## Required Fields

- `last_name`
- `state`

## ZIP Code

Accept:

- 5 digit ZIP
- ZIP+4 format
- 9 digit ZIP without hyphen

Examples:

```text
90004
90004-3000
900043000
```

Normalize before API call:

```js
postal_code = postal_code.replace('-', '');
```

---

# Recommended API Query Strategy

## Minimum Search

```text
last_name + state
```

## Preferred Search

```text
first_name + last_name + state
```

## Best Match Accuracy

```text
first_name + last_name + city + state + postal_code
```

---

# Example API Request

```http
GET https://npiregistry.cms.hhs.gov/api/?version=2.1&first_name=John&last_name=Smith&city=Los%20Angeles&state=CA&postal_code=90004&limit=10
```

---

# Dynamic Query Builder Logic

## Rules

Only append optional parameters when values exist.

---

# JavaScript Example

```js
const params = new URLSearchParams({
  version: '2.1',
  last_name,
  state,
  limit: 10
});

if (first_name) {
  params.append('first_name', first_name);
}

if (city) {
  params.append('city', city);
}

if (postal_code) {
  params.append(
    'postal_code',
    postal_code.replace('-', '')
  );
}

const url = `https://npiregistry.cms.hhs.gov/api/?${params.toString()}`;
```

---

# Expected Response Structure

## Example Response

```json
{
  "result_count": 1,
  "results": [
    {
      "number": "1234567890",
      "enumeration_type": "NPI-1",
      "basic": {
        "first_name": "JOHN",
        "last_name": "SMITH",
        "credential": "MD"
      },
      "taxonomies": [
        {
          "desc": "Internal Medicine"
        }
      ],
      "addresses": [
        {
          "city": "LOS ANGELES",
          "state": "CA",
          "postal_code": "900043000"
        }
      ]
    }
  ]
}
```

---

# Fields To Extract

## Provider Details

| Response Field | Usage |
|---|---|
| results[].number | NPI Number |
| results[].basic.first_name | First Name |
| results[].basic.last_name | Last Name |
| results[].basic.credential | Credential |
| results[].enumeration_type | Provider Type |
| results[].taxonomies[].desc | Specialty |
| results[].addresses[] | Address Info |

---

# Recommended UI Result Display

Display:

- Provider Name
- Credential
- NPI Number
- Specialty
- City / State

Example:

```text
John Smith, MD
NPI: 1234567890
Internal Medicine
Los Angeles, CA
```

---

# Empty State Handling

## No Results

Show:

```text
No matching NPI records found.
Please verify the provider information and try again.
```

---

# Error Handling

## API Failure

Handle:

- Timeout
- Network failure
- Invalid response
- Empty results

Recommended fallback:

```text
Unable to retrieve NPI information at this time.
Please try again later.
```

---

# Performance Recommendations

- Add debounce on frontend search (300–500ms)
- Avoid triggering API calls on every keystroke
- Use submit-based search preferred
- Cache recent search responses if applicable

---

# Security Recommendations

- Prefer backend proxy instead of direct frontend API call
- Sanitize all query parameters
- Validate state code format
- Prevent excessive repeated requests

---

# Suggested Backend Service Structure

## Service Name

```text
NpiLookupService
```

---

# Suggested Methods

```text
buildQuery()
searchProviders()
normalizePostalCode()
formatProviderResult()
```

---

# Example Backend Flow

```text
User Form Submit
    ↓
Validate Required Fields
    ↓
Normalize ZIP Code
    ↓
Build API Query
    ↓
Call CMS NPI API
    ↓
Parse Response
    ↓
Format Provider Results
    ↓
Return to UI
```

---

# Suggested API Response Mapping

| API Field | UI Field |
|---|---|
| number | NPI Number |
| basic.first_name | First Name |
| basic.last_name | Last Name |
| basic.credential | Credential |
| taxonomies[0].desc | Specialty |
| addresses[0].city | City |
| addresses[0].state | State |

---

# Suggested Frontend UX

## Search Button

Label:

```text
Find NPI Number
```

## Loading State

```text
Searching provider information...
```

## Result Selection

If multiple providers are returned:
- Show selectable list
- Allow user to choose provider
- Populate selected NPI into form

---

# Suggested Backend Architecture

## Recommended Layers

- Controller
- Service
- API Client
- Response Formatter

---

# Suggested Logging

Log:
- API request parameters
- API response time
- Empty result searches
- API failures

Do not log:
- Sensitive PHI/PII data unnecessarily

---

# Suggested Caching

Optional:
- Cache successful searches for short duration
- Recommended TTL: 24 hours

---

# Important Notes

- API authentication is NOT required
- API is publicly accessible
- CMS updates NPPES data daily
- State should use 2-letter US abbreviation

Examples:

```text
CA
NY
TX
FL
```

---

# Optional Future Enhancements

- Autocomplete provider search
- Specialty filtering
- Organization/provider toggle
- Pagination support
- Provider detail modal
- Cached search history
- Analytics tracking
- Retry mechanism for failed API requests
