<?php

namespace Drupal\ces_pages;

use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension to hash an input string.
 */
class TwigExtension extends AbstractExtension {

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new MinorUnitsConverter object.
   *
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The file URL generator.
   */
  public function __construct(FileUrlGeneratorInterface $fileUrlGenerator) {
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('get_field_by_node_id', [$this, 'getFieldByNodeId']),
      new TwigFunction('get_image_uri_by_id', [$this, 'getImageUriById']),
      new TwigFunction('get_field_value_by_paragraph_id', [$this, 'getFieldValueByParagraphId']),
    ];
  }

  /**
   * Get Field Url By Node Id.
   *
   * @return mixed
   *   Return mixed.
   */
  public function getFieldByNodeId(int $id, string $field_name): mixed {
    if (empty($id) || empty($field_name) || !is_numeric($id)) {
      return '';
    }

    $node = Node::load($id);
    if (empty($node)) {
      return '';
    }

    if (count($node->get($field_name)->getValue()) > 1) {
      $values = $node->get($field_name)->getValue();
    }
    else {
      if (!empty($node->get($field_name)->getValue())) {
        $values = $node->get($field_name)->getValue()[0];
      }
      else {
        $values = '';
      }
    }

    if (is_array($values) && isset($values['target_id'])) {
      $media = Media::load($values['target_id']);
      if (empty($media)) {
        $uri = File::load($values['target_id'])->getFileUri();
      }
      else {
        $fid = $media->field_media_image->target_id;
        $uri = File::load($fid)->getFileUri();
      }
      return $this->fileUrlGenerator->generateAbsoluteString($uri);
    }
    elseif (is_array($values) && isset($values['color'])) {
      return $values['color'];
    }
    elseif (is_array($values) && isset($values['uri'])) {
      return $values['uri'];
    }
    elseif (is_array($values) && isset($values['value'])) {
      if (str_contains($values['value'], '<p>')) {
        return strip_tags($values['value']);
      }
      else {
        return $values['value'];
      }
    }
    elseif (is_array($values) && count($values) > 1) {
      $results = [];
      foreach ($values as $value) {
        if (is_array($value) && isset($value['target_id'])) {
          $media = Media::load($value['target_id']);
          if (empty($media)) {
            $uri = File::load($value['target_id'])->getFileUri();
          }
          else {
            $fid = $media->field_media_image->target_id;
            $uri = File::load($fid)->getFileUri();
          }
          array_push($results, $this->fileUrlGenerator->generateAbsoluteString($uri));
        }
      }
      return $results;
    }
    else {
      return '';
    }
  }

  /**
   * Get Image URI by Id.
   *
   * @return string
   *   A uri string.
   */
  public function getImageUriById(int $image_id): string {
    if (empty($image_id) && !is_numeric($image_id)) {
      return '';
    }
    $media = Media::load($image_id);
    $fid = $media->getSource()->getSourceFieldValue($media);
    $file = File::load($fid);
    return $file->getFileUri();
  }

  /**
   * Get Field value by Paragraph Id.
   *
   * @return string
   *   A uri string.
   */
  public function getFieldValueByParagraphId(int|NULL $id, string $field_name): string {
    if (empty($id) || empty($field_name) || !is_numeric($id)) {
      return '';
    }
    $paragraph = Paragraph::load($id);
    if (empty($paragraph)) {
      return '';
    }

    if ($paragraph->hasField($field_name) && !empty($paragraph->get($field_name)->getValue())) {
      $value = $paragraph->get($field_name)->getValue()[0];
    }
    else {
      $value = '';
    }

    if (is_array($value) && isset($value['value'])) {
      return $value['value'];
    }
    else {
      return '';
    }
  }
}
