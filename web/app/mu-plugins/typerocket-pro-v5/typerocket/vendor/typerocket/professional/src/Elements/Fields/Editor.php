<?php
namespace TypeRocketPro\Elements\Fields;

use TypeRocketPro\Elements\Traits\EditorScripts;
use TypeRocket\Html\Html;
use TypeRocket\Elements\Fields\Textarea;
use TypeRocket\Elements\Fields\ScriptField;


class Editor extends Textarea implements ScriptField
{
    /**
     * Run on construction
     */
    protected function init()
    {
        $this->setType( 'editor' );
    }

    /**
     * Get the scripts
     */
    public function enqueueScripts() {
        EditorScripts::enqueueEditorScripts();
    }

    /**
     * Covert Editor to HTML string
     */
    public function getString()
    {
        $this->setupInputId();
        $this->setAttribute('data-tr-field', $this->getContextId());
        $this->setAttribute('name', $this->getNameAttributeString());
        $value = $this->setCast('string')->getValue();

        $this->attrClass('tr-editor');
        $value = $this->sanitize($value, 'editor' );

        return Html::textarea( $this->getAttributes(), $value )->getString();
    }

    /**
     * Set Editor Settings
     *
     * JSON encoded value.
     *
     * @param string|object $settings
     *
     * @return $this
     */
    public function setEditorSettings($settings)
    {
        return $this->setAttribute('data-settings', is_string($settings) ? $settings: json_encode($settings));
    }

}
