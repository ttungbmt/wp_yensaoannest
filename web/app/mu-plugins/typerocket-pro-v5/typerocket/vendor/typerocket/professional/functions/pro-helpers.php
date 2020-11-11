<?php
/**
 * Add SEO Meta Fields
 *
 * @param \TypeRocket\Elements\BaseForm|\App\Elements\Form $form
 * @param null|\TypeRocket\Elements\Tabs $tabs
 *
 * @return false|string
 *
 * @throws \Exception
 */
function tr_seo_meta_fields(\TypeRocket\Elements\BaseForm $form, $tabs = null)
{
    ob_start();
    (new \TypeRocketPro\Extensions\Seo\PostTypeMeta)->fields($form, $tabs);
    return ob_get_clean();
}

/**
 * Create Table
 *
 * @param \TypeRocket\Models\Model|string|null $model
 * @param int $limit
 *
 * @return \TypeRocketPro\Elements\Table
 */
function tr_table($model = null, $limit = 25)
{
    return new \TypeRocketPro\Elements\Table($model, $limit);
}

/**
 * Template Router
 *
 * @param callable|array|string|null $handler
 * @param array $args passed values to handler's method
 * @param array $construct passed values to handler's constructor
 *
 * @return \TypeRocketPro\Http\Template|null
 */
function tr_template_router($handler = null, $args = [], $construct = [])
{
    if($handler) {
        \TypeRocketPro\Http\Template::respond($handler, $args, $construct);
    } else {
        return new \TypeRocketPro\Http\Template;
    }

    return null;
}

/**
 * @param int|string $arg
 *
 * @return mixed
 */
function tr_image_cache(int $arg)
{
    return \TypeRocketPro\Utility\ImageCache::get($arg);
}

/**
 * @param array|\Traversable $data
 * @param null|string $dots
 *
 * @return mixed
 */
function tr_image_cache_index($data, $dots = null)
{
    return \TypeRocketPro\Utility\ImageCache::index($data, $dots);
}

/**
 * @param int|string $id
 * @param string $size
 *
 * @return mixed|string|null
 */
function tr_image_src($id, $size = 'full')
{
    return \TypeRocketPro\Utility\ImageCache::attachmentSrc($id, $size);
}