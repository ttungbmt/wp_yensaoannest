<?php
namespace TypeRocketPro\Elements\Traits;

use TypeRocketPro\Elements\Fields\Background;
use TypeRocketPro\Elements\Fields\Checkboxes;
use TypeRocketPro\Elements\Fields\Editor;
use TypeRocketPro\Elements\Fields\Gallery;
use TypeRocketPro\Elements\Fields\Location;
use TypeRocketPro\Elements\Fields\Range;
use TypeRocketPro\Elements\Fields\Swatches;
use TypeRocketPro\Elements\Fields\Textexpand;
use TypeRocketPro\Elements\Fields\Url;

trait AdvancedFields
{
    /**
     * URL Input
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool $label
     *
     * @return Url
     */
    public function url( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Url( $name, $attr, $settings, $label, $this );
    }

    /**
     * Range
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool|true $label
     *
     * @return Range
     */
    public function range( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Range( $name, $attr, $settings, $label, $this );
    }

    /**
     * Textexpand Input
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool|true $label
     *
     * @return Textexpand
     */
    public function textexpand( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Textexpand( $name, $attr, $settings, $label, $this );
    }

    /**
     * Checkboxes Input
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool|true $label
     *
     * @return Checkboxes
     */
    public function checkboxes( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Checkboxes( $name, $attr, $settings, $label, $this );
    }

    /**
     * Background Input
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool|true $label
     *
     * @return Background
     */
    public function background( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Background( $name, $attr, $settings, $label, $this );
    }

    /**
     * Gallery Input
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool|true $label
     *
     * @return Gallery
     */
    public function gallery( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Gallery( $name, $attr, $settings, $label, $this );
    }

    /**
     * Swatches Input
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool|true $label
     *
     * @return Swatches
     */
    public function swatches( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Swatches( $name, $attr, $settings, $label, $this );
    }

    /**
     * Location Inputs
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool|true $label
     *
     * @return Location
     */
    public function location( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Location( $name, $attr, $settings, $label, $this );
    }

    /**
     * Editor Input
     *
     * @param string $name
     * @param array $attr
     * @param array $settings
     * @param bool|true $label
     *
     * @return Editor
     */
    public function editor( $name, array $attr = [], array $settings = [], $label = true )
    {
        return new Editor( $name, $attr, $settings, $label, $this );
    }
}