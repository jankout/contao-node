<?php

/*
 * Node Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2019, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

namespace Terminal42\NodeBundle;

use Contao\ContentModel;
use Contao\Controller;
use Terminal42\NodeBundle\Model\NodeModel;

class NodeManager
{
    /**
     * Generate single node.
     *
     * @param int $id
     *
     * @return string|null
     */
    public function generateSingle(int $id): ?string
    {
        if (!$id) {
            return null;
        }

        if (null === ($nodeModel = NodeModel::findOneBy(['id=?', 'type=?'], [$id, NodeModel::TYPE_CONTENT]))) {
            return null;
        }

        return $this->generateBuffer($nodeModel);
    }

    /**
     * Generate multiple nodes.
     *
     * @param array $ids
     *
     * @return array
     */
    public function generateMultiple(array $ids): array
    {
        $ids = array_filter($ids);

        if (0 === \count($ids)) {
            return [];
        }

        $nodeModels = NodeModel::findBy(
            ['id IN ('.implode(',', $ids).')', 'type=?'],
            [NodeModel::TYPE_CONTENT, implode(',', $ids)],
            ['order' => 'FIND_IN_SET(`id`, ?)']
        );

        if (null === $nodeModels) {
            return [];
        }

        $nodes = [];

        /** @var NodeModel $nodeModel */
        foreach ($nodeModels as $nodeModel) {
            $nodes[$nodeModel->id] = $this->generateBuffer($nodeModel);
        }

        return array_filter($nodes);
    }

    /**
     * Generate the node buffer (content elements).
     *
     * @param NodeModel $nodeModel
     *
     * @return string
     */
    private function generateBuffer(NodeModel $nodeModel): string
    {
        $buffer = '';

        if (null !== ($elements = $nodeModel->getContentElements())) {
            /** @var ContentModel $element */
            foreach ($elements as $element) {
                $buffer .= Controller::getContentElement($element);
            }
        }

        return $buffer;
    }
}
