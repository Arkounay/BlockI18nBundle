<?php

namespace Arkounay\BlockI18nBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;



/**
 * Page Block entity. Represents an HTML block that will be displayed in a page.
 * @ORM\Entity(repositoryClass="PageBlockRepository")
 * @ORM\Table(name="page_block")
 * @UniqueEntity(fields = {"id"})
 */
class PageBlock
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=40)
     * @Assert\NotBlank();
     */
    protected $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

}
