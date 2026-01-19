# API

## Normalized property format

API endpoints in `/local/api` return iblock properties in a normalized structure. Each property value is an object with the following keys:

- `type`: the property type from `PROPERTY_TYPE`. If `USER_TYPE` is set, it is appended as `PROPERTY_TYPE:USER_TYPE`.
- `multiple`: `true` when `MULTIPLE` is `Y`, otherwise `false`.
- `value`: present for single-value properties.
- `values`: present for multi-value properties.

### Example

```json
{
  "price": {
    "type": "N",
    "multiple": false,
    "value": 12000
  },
  "tags": {
    "type": "S",
    "multiple": true,
    "values": ["b2b", "marketing"]
  }
}
```
