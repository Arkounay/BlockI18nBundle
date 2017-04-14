<?php


namespace Arkounay\BlockI18nBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BlockService extends \Twig_Extension
{
    private $em;
    private $authorizationChecker;
    private $roles;

    public function __construct(EntityManager $em, AuthorizationCheckerInterface $authorizationChecker, array $roles)
    {
        $this->em = $em;
        $this->authorizationChecker = $authorizationChecker;
        $this->roles = $roles['roles'];
    }

    /**
     * @param $blockId
     * @param bool $isEditable
     * @return string The HTML inside the block.
     * Will be surrounded by a special span if has the permissions to edit.
     * Will be empty if not found in the database.
     */
    public function renderSpanBlock($blockId, $isEditable = true)
    {
        return $this->removeP($this->renderBlock($blockId, $isEditable, 'span', true));
    }

    /**
     * @param $blockId
     * @param bool $isEditable
     * @param string $dom The HTML element. Div by default.
     * @return string The HTML inside the block.
     * Will be surrounded by a special div if has the permissions to edit.
     * Will be empty if not found in the database.
     */
    public function renderBlock($blockId, $isEditable = true, $dom = 'div', $isLight = false)
    {
        $pageBlock = $this->em->getRepository('ArkounayBlockI18nBundle:PageBlock')->find($blockId);
        $res = '';
        if ($pageBlock !== null) {
            $res = $pageBlock->getContent();
        }
        if ($this->hasInlineEditPermissions() && $isEditable) {
            $class = 'js-arkounay-block-bundle-editable';
            if  ($isLight) {
                $class .= ' js-arkounay-block-light';
            } else {
                $class .= ' js-arkounay-block-bundle-block';
            }
            $res = '<' . $dom . ' class="' . $class . '" data-id="' . $blockId . '">' . $res . '</' . $dom . '>';
        }
        return $res;
    }

    private function removeP($value)
    {
        return preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '$1',  $value);
    }

    /**
     * @return bool True if the current user has permissions to edit HTML inline.
     */
    public function hasInlineEditPermissions()
    {
        try {
            return $this->authorizationChecker->isGranted($this->roles);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function renderEntityFieldTwig($entity, $field)
    {
        return $this->renderEntityField($entity, $field, false);
    }

    /**
     * @param $entity object The Entity object that owns the field which will be edited
     * @param $field string The field name of the entity to edit
     * @param $isPlain bool If true, the edition will have very few available options in TinyMCE
     * @return string The HTML inside the block.
     * Will be surrounded by a special div if has the permissions to edit.
     * Will be empty if not found in the database.
     */
    private function renderEntityField($entity, $field, $isPlain)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        try {
            $res = $accessor->getValue($entity, $field);
        } catch (NoSuchPropertyException $e) {
            if (method_exists($entity, 'proxyCurrentLocaleTranslation')) {
                $res = call_user_func_array(
                    [$entity, 'get' . ucfirst($field)],
                    []
                );
            }
        }
        $class = 'js-arkounay-block-bundle-entity';
        $tree = 'div';

        if ($isPlain) {
            $class .= '-plain';
            $tree = 'span';
        }

        if ($this->hasInlineEditPermissions()) {
            $res = '<' . $tree . ' class="js-arkounay-block-bundle-editable ' . $class . '" data-field="' . $field . '" data-entity="' . get_class($entity) . '" data-id="' . $entity->getId() . '">' . $res . '</' . $tree . '>';
        }

        return $res;
    }

    public function renderEntityFieldPlainTextTwig($entity, $field)
    {
        return $this->removeP($this->renderEntityField($entity, $field, true));
    }

    public function getFunctions()
    {
        return [
            'render_block' => new \Twig_SimpleFunction('render_block', [$this, 'renderBlock'], ['is_safe' => ['html']]),
            'render_span_block' => new \Twig_SimpleFunction('render_span_block', [$this, 'renderSpanBlock'], ['is_safe' => ['html']]),
            'render_entity_field' => new \Twig_SimpleFunction('render_entity_field', [$this, 'renderEntityFieldTwig'], ['is_safe' => ['html']]),
            'render_plain_entity_field' => new \Twig_SimpleFunction('render_plain_entity_field', [$this, 'renderEntityFieldPlainTextTwig'], ['is_safe' => ['html']]),
            'has_inline_edit_permissions' => new \Twig_SimpleFunction('has_inline_edit_permissions', [$this, 'hasInlineEditPermissions'])
        ];
    }
}