<?php
namespace verbb\formie\base;

use verbb\formie\elements\Submission;
use verbb\formie\events\SendIntegrationPayloadEvent;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;

use yii\helpers\Markdown;

abstract class Crm extends Integration
{
    // Static Methods
    // =========================================================================

    public static function typeName(): string
    {
        return Craft::t('formie', 'CRM');
    }


    // Properties
    // =========================================================================
    
    public ?string $optInField = null;


    // Public Methods
    // =========================================================================

    public function getIconUrl(): string
    {
        $handle = $this->getClassHandle();

        return Craft::$app->getAssetManager()->getPublishedUrl("@verbb/formie/web/assets/cp/dist/img/crm/{$handle}.svg", true);
    }

    public function getSettingsHtml(): ?string
    {
        $handle = $this->getClassHandle();

        return Craft::$app->getView()->renderTemplate("formie/integrations/crm/{$handle}/_plugin-settings", [
            'integration' => $this,
        ]);
    }

    public function getFormSettingsHtml($form): string
    {
        $handle = $this->getClassHandle();

        return Craft::$app->getView()->renderTemplate("formie/integrations/crm/{$handle}/_form-settings", [
            'integration' => $this,
            'form' => $form,
        ]);
    }

    public function getFieldMappingValues(Submission $submission, $fieldMapping, $fieldSettings = [])
    {
        // A quick shortcut to keep CRM's simple, just pass in a string to the namespace
        if (is_string($fieldSettings)) {
            $fields = $this->getFormSettingValue($fieldSettings);
        } else {
            $fields = $fieldSettings;
        }

        return parent::getFieldMappingValues($submission, $fieldMapping, $fields);
    }

    public function beforeSendPayload(Submission $submission, &$endpoint, &$payload, &$method): bool
    {
        // If in the context of a queue. save the payload for debugging
        if ($this->getQueueJob()) {
            $this->getQueueJob()->payload = $payload;
        }

        $event = new SendIntegrationPayloadEvent([
            'submission' => $submission,
            'payload' => $payload,
            'endpoint' => $endpoint,
            'method' => $method,
            'integration' => $this,
        ]);
        $this->trigger(self::EVENT_BEFORE_SEND_PAYLOAD, $event);

        if (!$event->isValid) {
            Integration::info($this, 'Sending payload cancelled by event hook.');
        }

        // Also, check for opt-in fields. This allows the above event to potentially alter things
        if (!$this->enforceOptInField($submission)) {
            Integration::info($this, 'Sending payload cancelled by opt-in field.');

            return false;
        }

        // Allow events to alter some props
        $payload = $event->payload;
        $endpoint = $event->endpoint;
        $method = $event->method;

        return $event->isValid;
    }

    public function getFrontEndJsVariables($field = null): ?array
    {
        return null;
    }

    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl('formie/settings/crm/edit/' . $this->id);
    }
}
