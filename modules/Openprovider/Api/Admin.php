<?php

/**
 * OpenProvider DNS Admin API
 * Provides admin API endpoints for managing DNS zones and records via OpenProvider.
 */

namespace Box\Mod\Openprovider\Api;

class Admin extends \Api_Abstract
{
    // --- Zone Endpoints ---

    /**
     * List DNS zones.
     * Optional: name_pattern, page, per_page
     */
    public function zone_get_list($data): array
    {
        return $this->getService()->getZoneList($data);
    }

    /**
     * Get a single DNS zone with records.
     * Required: name (zone name, e.g. "example.com")
     */
    public function zone_get($data): array
    {
        if (empty($data['name'])) {
            throw new \FOSSBilling\InformationException('Zone name is required.');
        }
        return $this->getService()->getZone($data['name']);
    }

    /**
     * Create a new DNS zone.
     * Required: domain_name, extension
     * Optional: records (array), secured (bool), provider (string)
     */
    public function zone_create($data): array
    {
        if (empty($data['domain_name']) || empty($data['extension'])) {
            throw new \FOSSBilling\InformationException('Domain name and extension are required.');
        }
        return $this->getService()->createZone(
            $data['domain_name'],
            $data['extension'],
            $data['records'] ?? [],
            $data['secured'] ?? false,
            $data['provider'] ?? ''
        );
    }

    /**
     * Delete a DNS zone.
     * Required: name
     */
    public function zone_delete($data): array
    {
        if (empty($data['name'])) {
            throw new \FOSSBilling\InformationException('Zone name is required.');
        }
        return $this->getService()->deleteZone($data['name']);
    }

    // --- Record Endpoints ---

    /**
     * List records for a zone.
     * Required: zone_name
     * Optional: type
     */
    public function record_get_list($data): array
    {
        if (empty($data['zone_name'])) {
            throw new \FOSSBilling\InformationException('Zone name is required.');
        }
        return $this->getService()->getRecords($data['zone_name'], $data);
    }

    /**
     * Add records to a zone.
     * Required: zone_name, records (array of {name, type, value, ttl, prio})
     */
    public function record_add($data): array
    {
        if (empty($data['zone_name']) || empty($data['records'])) {
            throw new \FOSSBilling\InformationException('Zone name and records are required.');
        }
        $records = is_string($data['records']) ? json_decode($data['records'], true) : $data['records'];
        return $this->getService()->addRecords($data['zone_name'], $records);
    }

    /**
     * Update a single record in a zone.
     * Required: zone_name, original_record, new_record
     * Each record: {name, type, value, ttl, prio}
     */
    public function record_update($data): array
    {
        if (empty($data['zone_name']) || empty($data['original_record']) || empty($data['new_record'])) {
            throw new \FOSSBilling\InformationException('Zone name, original record, and new record are required.');
        }
        $original = is_string($data['original_record']) ? json_decode($data['original_record'], true) : $data['original_record'];
        $new = is_string($data['new_record']) ? json_decode($data['new_record'], true) : $data['new_record'];
        return $this->getService()->updateRecord($data['zone_name'], $original, $new);
    }

    /**
     * Remove records from a zone.
     * Required: zone_name, records (array of {name, type, value, ttl, prio})
     */
    public function record_delete($data): array
    {
        if (empty($data['zone_name']) || empty($data['records'])) {
            throw new \FOSSBilling\InformationException('Zone name and records are required.');
        }
        $records = is_string($data['records']) ? json_decode($data['records'], true) : $data['records'];
        return $this->getService()->removeRecords($data['zone_name'], $records);
    }

    // --- Template Endpoints ---

    /**
     * List DNS templates.
     */
    public function template_get_list($data): array
    {
        return $this->getService()->getTemplateList();
    }

    /**
     * Create a DNS template.
     * Required: name, records (array)
     */
    public function template_create($data): array
    {
        if (empty($data['name']) || empty($data['records'])) {
            throw new \FOSSBilling\InformationException('Template name and records are required.');
        }
        $records = is_string($data['records']) ? json_decode($data['records'], true) : $data['records'];
        return $this->getService()->createTemplate($data['name'], $records);
    }

    /**
     * Delete a DNS template.
     * Required: id
     */
    public function template_delete($data): array
    {
        if (empty($data['id'])) {
            throw new \FOSSBilling\InformationException('Template ID is required.');
        }
        return $this->getService()->deleteTemplate((int)$data['id']);
    }
}
