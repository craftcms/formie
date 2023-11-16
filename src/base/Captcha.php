<?php
namespace verbb\formie\base;

use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;

use Craft;
use craft\helpers\StringHelper;

abstract class Captcha extends Integration
{
    // Static Methods
    // =========================================================================

    public static function typeName(): string
    {
        return Craft::t('formie', 'Captchas');
    }

    public static function supportsConnection(): bool
    {
        return false;
    }

    public static function supportsPayloadSending(): bool
    {
        return false;
    }

    // Properties
    // =========================================================================

    public bool $showAllPages = false;
    public ?string $spamReason = null;
    public ?bool $saveSpam = null;


    // Public Methods
    // =========================================================================

    public function getIconUrl(): string
    {
        $handle = StringHelper::toKebabCase($this->getHandle());

        return Craft::$app->getAssetManager()->getPublishedUrl("@verbb/formie/web/assets/cp/dist/img/captchas/{$handle}.svg", true);
    }

    public function getFormSettingsHtml($form): string
    {
        return Craft::$app->getView()->renderTemplate('formie/integrations/captchas/_form-settings', [
            'integration' => $this,
            'form' => $form,
        ]);
    }

    public function getFrontEndHtml(Form $form, $page = null): string
    {
        return '';
    }

    public function getFrontEndJsVariables(Form $form, $page = null): ?array
    {
        return null;
    }

    public function getRefreshJsVariables(Form $form, $page = null): ?array
    {
        return null;
    }

    public function validateSubmission(Submission $submission): bool
    {
        return true;
    }

    public function getGqlHandle(): string
    {
        return StringHelper::toCamelCase($this->handle . 'Captcha');
    }


    // Protected Methods
    // =========================================================================

    protected function getOrSet($key, $callable)
    {
        if ($value = Craft::$app->getSession()->get($key)) {
            return $value;
        }

        $value = $callable($this);

        Craft::$app->getSession()->set($key, $value);

        return $value;
    }

    protected function getRequestParam($name, $allowEmptyString = false)
    {
        // Handle the traditional param, as a POST param
        $param = Craft::$app->getRequest()->getParam($name);

        if (($allowEmptyString && $param === '') || $param) {
            return $param;
        }

        // Handle the param being set in a GQL mutation
        $param = Craft::$app->getRequest()->getParam('variables.' . $this->getGqlHandle());
        $paramName = $param['name'] ?? null;

        if ($paramName === $name) {
            if (($allowEmptyString && $param === '') || $param) {
                return $param['value'] ?? null;
            }
        }

        return null;
    }
}
