<?php

/*
 * This file is part of the Superdesk Web Publisher Content Bundle.
 *
 * Copyright 2016 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2016 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\ContentBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use SWP\Component\Common\Model\TimestampableTrait;

class Image implements ImageInterface
{
    use TimestampableTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * Uploaded file extension.
     *
     * @var string
     */
    protected $fileExtension;

    /**
     * @var string
     */
    protected $assetId;

    /**
     * @var ArticleMediaInterface
     */
    protected $media;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var ArrayCollection
     */
    protected $renditions;

    /**
     * File constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setRenditions(new ArrayCollection());
    }

    /**
     * @return ArticleMediaInterface
     */
    public function getMedia(): ArticleMediaInterface
    {
        return $this->media;
    }

    /**
     * @param ArticleMediaInterface $media
     */
    public function setMedia(ArticleMediaInterface $media)
    {
        $this->media = $media;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setFileExtension($extension)
    {
        $this->fileExtension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * @return string
     */
    public function getAssetId(): string
    {
        return $this->assetId;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssetId(string $assetId)
    {
        $this->assetId = $assetId;
    }

    /**
     * @return ArrayCollection
     */
    public function getRenditions()
    {
        return $this->renditions;
    }

    /**
     * @param ImageRendition $rendition
     */
    public function addRendition(ImageRendition $rendition)
    {
        $this->renditions->add($rendition);
    }

    /**
     * @param ArrayCollection $renditions
     */
    public function setRenditions($renditions)
    {
        $this->renditions = $renditions;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the value of Width.
     *
     * @param int $width
     *
     * @return self
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the value of Height.
     *
     * @param int $height
     *
     * @return self
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }
}
