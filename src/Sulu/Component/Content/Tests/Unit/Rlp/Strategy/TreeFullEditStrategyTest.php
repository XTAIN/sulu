<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\Tests\Unit\ResourceLocator\Strategy;

use PHPCR\NodeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\DocumentManagerBundle\Bridge\DocumentInspector;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Component\Content\Compat\StructureInterface;
use Sulu\Component\Content\Compat\StructureManagerInterface;
use Sulu\Component\Content\ContentTypeManagerInterface;
use Sulu\Component\Content\Exception\ResourceLocatorAlreadyExistsException;
use Sulu\Component\Content\Exception\ResourceLocatorNotValidException;
use Sulu\Component\Content\Types\ResourceLocator\Mapper\ResourceLocatorMapperInterface;
use Sulu\Component\Content\Types\ResourceLocator\ResourceLocatorInformation;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\TreeFullEditStrategy;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\TreeGenerator;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\TreeLeafEditStrategy;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\PHPCR\PathCleanupInterface;
use Sulu\Component\Util\SuluNodeHelper;

class TreeFullEditStrategyTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ResourceLocatorMapperInterface
     */
    private $mapper;

    /**
     * @var PathCleanupInterface
     */
    private $cleaner;

    /**
     * @var StructureManagerInterface
     */
    private $structureManager;

    /**
     * @var ContentTypeManagerInterface
     */
    private $contentTypeManager;

    /**
     * @var SuluNodeHelper
     */
    private $nodeHelper;

    /**
     * @var DocumentInspector
     */
    private $documentInspector;

    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    /**
     * @var TreeLeafEditStrategy
     */
    private $treeStrategy;

    public function setUp(): void
    {
        $this->mapper = $this->prophesize(ResourceLocatorMapperInterface::class);
        $this->cleaner = $this->prophesize(PathCleanupInterface::class);
        $this->structureManager = $this->prophesize(StructureManagerInterface::class);
        $this->contentTypeManager = $this->prophesize(ContentTypeManagerInterface::class);
        $this->nodeHelper = $this->prophesize(SuluNodeHelper::class);
        $this->documentInspector = $this->prophesize(DocumentInspector::class);
        $this->documentManager = $this->prophesize(DocumentManagerInterface::class);

        $this->treeStrategy = new TreeFullEditStrategy(
            $this->mapper->reveal(),
            $this->cleaner->reveal(),
            $this->structureManager->reveal(),
            $this->contentTypeManager->reveal(),
            $this->nodeHelper->reveal(),
            $this->documentInspector->reveal(),
            $this->documentManager->reveal(),
            new TreeGenerator()
        );
    }

    public function testGetChildPart()
    {
        $this->assertEquals('test/asdf', $this->treeStrategy->getChildPart('/test/asdf'));
        $this->assertEquals('asdf', $this->treeStrategy->getChildPart('/asdf'));
        $this->assertEquals('asdf', $this->treeStrategy->getChildPart('asdf'));
    }

    public function testGenerate()
    {
        $title = 'new-page';
        $parentUuid = 'uuid-uuid-uuid-uuid';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $parent = $this->prophesize(PageDocument::class);
        $parent->getPublished()->willReturn(true);

        $this->documentManager->find($parentUuid, $languageCode, ['load_ghost_content' => false])->willReturn($parent);
        $this->documentInspector->getUuid($parent)->willReturn($parentUuid);
        $this->mapper->loadByContentUuid($parentUuid, $webspaceKey, $languageCode, null)->willReturn('path/to/parent');
        $this->cleaner->cleanup('path/to/parent/new-page', $languageCode)->willReturn('path/to/parent/new-page');
        $this->mapper->getUniquePath('path/to/parent/new-page', $webspaceKey, $languageCode, null, null)->willReturn(
            'path/to/parent/new-page'
        );

        $result = $this->treeStrategy->generate($title, $parentUuid, $webspaceKey, $languageCode);
        $this->assertEquals('path/to/parent/new-page', $result);
    }

    public function testGenerateWithSegmentKey()
    {
        $title = 'new-page';
        $parentUuid = 'uuid-uuid-uuid-uuid';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';
        $segmentKey = 'segment';

        $parent = $this->prophesize(PageDocument::class);
        $parent->getPublished()->willReturn(true);

        $this->documentManager->find($parentUuid, $languageCode, ['load_ghost_content' => false])->willReturn($parent);
        $this->documentInspector->getUuid($parent)->willReturn($parentUuid);
        $this->mapper->loadByContentUuid($parentUuid, $webspaceKey, $languageCode, null)->willReturn('path/to/parent');
        $this->cleaner->cleanup('path/to/parent/new-page', $languageCode)->willReturn('path/to/parent/new-page');
        $this->mapper->getUniquePath('path/to/parent/new-page', $webspaceKey, $languageCode, $segmentKey, null)->willReturn(
            'path/to/parent/new-page'
        );

        $result = $this->treeStrategy->generate($title, $parentUuid, $webspaceKey, $languageCode, $segmentKey);
        $this->assertEquals('path/to/parent/new-page', $result);
    }

    public function testGenerateWithUuid()
    {
        $title = 'new-page';
        $parentUuid = 'uuid-uuid-uuid-uuid';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';
        $uuid = 'another-uuid';

        $parent = $this->prophesize(PageDocument::class);
        $parent->getPublished()->willReturn(true);

        $this->documentManager->find($parentUuid, $languageCode, ['load_ghost_content' => false])->willReturn($parent);
        $this->documentInspector->getUuid($parent)->willReturn($parentUuid);
        $this->mapper->loadByContentUuid($parentUuid, $webspaceKey, $languageCode, null)->willReturn('path/to/parent');
        $this->cleaner->cleanup('path/to/parent/new-page', $languageCode)->willReturn('path/to/parent/new-page');
        $this->mapper->getUniquePath('path/to/parent/new-page', $webspaceKey, $languageCode, null, $uuid)->willReturn(
            'path/to/parent/new-page'
        );

        $result = $this->treeStrategy->generate($title, $parentUuid, $webspaceKey, $languageCode, null, $uuid);
        $this->assertEquals('path/to/parent/new-page', $result);
    }

    public function testGenerateWithoutParentUuid()
    {
        $title = 'new-page';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $parent = $this->prophesize(PageDocument::class);
        $parent->getPublished()->willReturn(true);

        $this->cleaner->cleanup('/new-page', $languageCode)->willReturn('/new-page');
        $this->mapper->getUniquePath('/new-page', $webspaceKey, $languageCode, null, null)->willReturn(
            'path/to/parent/new-page'
        );

        $result = $this->treeStrategy->generate($title, null, $webspaceKey, $languageCode);
        $this->assertEquals('path/to/parent/new-page', $result);
    }

    public function testSave()
    {
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $node = $this->prophesize(NodeInterface::class);
        $document = $this->prophesize(PageDocument::class);
        $document->getResourceSegment()->willReturn('path/to/doc');

        $this->documentInspector->getNode($document)->willReturn($node);
        $this->documentInspector->getWebspace($document)->willReturn($webspaceKey);
        $this->documentInspector->getOriginalLocale($document)->willReturn($languageCode);

        $this->mapper->loadByContent($node, $webspaceKey, $languageCode, null)->willReturn('old/path');
        $this->cleaner->validate('path/to/doc')->willReturn(true);
        $this->mapper->unique('path/to/doc', $webspaceKey, $languageCode)->willReturn(true);
        $this->mapper->save($document)->shouldBeCalled();

        $this->treeStrategy->save($document->reveal(), null);
    }

    public function testSaveSame()
    {
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $document = $this->prophesize(PageDocument::class);
        $document->getResourceSegment()->willReturn('path/to/doc');
        $node = $this->prophesize(NodeInterface::class);

        $this->documentInspector->getNode($document)->willReturn($node);
        $this->documentInspector->getWebspace($document)->willReturn($webspaceKey);
        $this->documentInspector->getOriginalLocale($document)->willReturn($languageCode);

        $this->mapper->loadByContent($node, $webspaceKey, $languageCode, null)->willReturn('path/to/doc')->shouldBeCalled();

        $this->treeStrategy->save($document->reveal(), null);
    }

    public function testSaveInvalid()
    {
        $this->expectException(ResourceLocatorNotValidException::class);

        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $document = $this->prophesize(PageDocument::class);
        $document->getResourceSegment()->willReturn('path/to/doc');
        $node = $this->prophesize(NodeInterface::class);

        $this->documentInspector->getNode($document)->willReturn($node);
        $this->documentInspector->getWebspace($document)->willReturn($webspaceKey);
        $this->documentInspector->getOriginalLocale($document)->willReturn($languageCode);

        $this->mapper->loadByContent($node, $webspaceKey, $languageCode, null)->willReturn('old/path');
        $this->cleaner->validate('path/to/doc')->willReturn(false);

        $this->treeStrategy->save($document->reveal(), null);
    }

    public function testSaveAlreadyExist()
    {
        $this->expectException(ResourceLocatorAlreadyExistsException::class);

        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $document = $this->prophesize(PageDocument::class);
        $document->getResourceSegment()->willReturn('path/to/doc');
        $node = $this->prophesize(NodeInterface::class);

        $this->documentInspector->getNode($document)->willReturn($node);
        $this->documentInspector->getWebspace($document)->willReturn($webspaceKey);
        $this->documentInspector->getOriginalLocale($document)->willReturn($languageCode);
        $this->documentInspector->getUuid($document)->willReturn('document-uuid-uuid');

        $this->mapper->loadByContent($node, $webspaceKey, $languageCode, null)->willReturn('old/path');
        $this->cleaner->validate('path/to/doc')->willReturn(true);
        $this->mapper->unique('path/to/doc', $webspaceKey, $languageCode)->willReturn(false);
        $this->mapper->loadByResourceLocator('path/to/doc', $webspaceKey, $languageCode, null)->willReturn(
            'other-uuid'
        );

        $this->treeStrategy->save($document->reveal(), null);
    }

    public function testSaveWithPublishedChild()
    {
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';
        $this->nodeHelper->getTranslatedPropertyName('template', $languageCode)->willReturn('template-prop');
        $structure = $this->prophesize(StructureInterface::class);
        $this->structureManager->getStructure('default')->willReturn($structure);

        $document = $this->prophesize(PageDocument::class);
        $document->getResourceSegment()->willReturn('path/to/doc');
        $document->getPublished()->willReturn(true);

        $node = $this->prophesize(NodeInterface::class);
        $this->documentInspector->getNode($document)->willReturn($node->reveal());
        $this->documentInspector->getWebspace($document)->willReturn($webspaceKey);
        $this->documentInspector->getOriginalLocale($document)->willReturn($languageCode);
        $this->documentInspector->getUuid($document)->willReturn('uuid-uuid-uuid-uuid');

        $this->mapper->loadByContent($node, $webspaceKey, $languageCode, null)->willReturn('old/path');
        $this->cleaner->validate('path/to/doc')->willReturn(true);
        $this->mapper->unique('path/to/doc', $webspaceKey, $languageCode)->willReturn(true);
        $this->mapper->save($document)->shouldBeCalled();

        $this->treeStrategy->save($document->reveal(), null);
    }

    public function testSaveWithUnpublishedChild()
    {
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';
        $this->nodeHelper->getTranslatedPropertyName('template', $languageCode)->willReturn('template-prop');
        $structure = $this->prophesize(StructureInterface::class);
        $this->structureManager->getStructure('default')->willReturn($structure);

        $document = $this->prophesize(PageDocument::class);
        $document->getResourceSegment()->willReturn('path/to/doc');
        $document->getPublished()->willReturn(true);

        $node = $this->prophesize(NodeInterface::class);

        $this->documentInspector->getNode($document)->willReturn($node);
        $this->documentInspector->getWebspace($document)->willReturn($webspaceKey);
        $this->documentInspector->getOriginalLocale($document)->willReturn($languageCode);
        $this->documentInspector->getUuid($document)->willReturn('uuid-uuid-uuid-uuid');

        $this->mapper->loadByContent($node, $webspaceKey, $languageCode, null)->willReturn('old/path');
        $this->cleaner->validate('path/to/doc')->willReturn(true);
        $this->mapper->unique('path/to/doc', $webspaceKey, $languageCode)->willReturn(true);
        $this->mapper->save($document)->shouldBeCalled();

        $this->treeStrategy->save($document->reveal(), null);
    }

    public function testLoadByContent()
    {
        $document = $this->prophesize(PageDocument::class);
        $node = $this->prophesize(NodeInterface::class);

        $this->documentInspector->getNode($document)->willReturn($node);
        $this->documentInspector->getWebspace($document)->willReturn('sulu_io');
        $this->documentInspector->getOriginalLocale($document)->willReturn('en');

        $this->mapper->loadByContent($node, 'sulu_io', 'en', null)->willReturn('path/to/document');

        $result = $this->treeStrategy->loadByContent($document->reveal());
        $this->assertEquals('path/to/document', $result);
    }

    public function testLoadByContentUuid()
    {
        $uuid = 'uuid-uuid-uuid-uuid';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $this->mapper->loadByContentUuid($uuid, $webspaceKey, $languageCode, null)->willReturn('path/to/document');

        $result = $this->treeStrategy->loadByContentUuid($uuid, $webspaceKey, $languageCode);
        $this->assertEquals('path/to/document', $result);
    }

    public function testLoadByContentUuidWithSegmentKey()
    {
        $uuid = 'uuid-uuid-uuid-uuid';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';
        $segmentKey = 'segment';

        $this->mapper->loadByContentUuid($uuid, $webspaceKey, $languageCode, $segmentKey)->willReturn(
            'path/to/document'
        );

        $result = $this->treeStrategy->loadByContentUuid($uuid, $webspaceKey, $languageCode, $segmentKey);
        $this->assertEquals('path/to/document', $result);
    }

    public function testLoadHistoryByContentUuid()
    {
        $uuid = 'uuid-uuid-uuid-uuid';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $resourceLocator = $this->prophesize(ResourceLocatorInformation::class);
        $resourceLocator->getResourceLocator()->willReturn('old/path');
        $this->mapper->loadHistoryByContentUuid($uuid, $webspaceKey, $languageCode, null)->willReturn(
            [$resourceLocator]
        );

        $result = $this->treeStrategy->loadHistoryByContentUuid($uuid, $webspaceKey, $languageCode);
        $this->assertEquals('old/path', $result[0]->getResourceLocator());
    }

    public function testLoadHistoryByContentUuidWithSegmentKey()
    {
        $uuid = 'uuid-uuid-uuid-uuid';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';
        $segmentKey = 'segment';

        $resourceLocator = $this->prophesize(ResourceLocatorInformation::class);
        $resourceLocator->getResourceLocator()->willReturn('old/path');
        $this->mapper->loadHistoryByContentUuid($uuid, $webspaceKey, $languageCode, $segmentKey)->willReturn(
            [$resourceLocator]
        );

        $result = $this->treeStrategy->loadHistoryByContentUuid($uuid, $webspaceKey, $languageCode, $segmentKey);
        $this->assertEquals('old/path', $result[0]->getResourceLocator());
    }

    public function testLoadByResourceLocator()
    {
        $resourceLocator = 'path/to/document';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';

        $this->mapper->loadByResourceLocator($resourceLocator, $webspaceKey, $languageCode, null)->willReturn('uuid');

        $result = $this->treeStrategy->loadByResourceLocator($resourceLocator, $webspaceKey, $languageCode);
        $this->assertEquals('uuid', $result);
    }

    public function testLoadByResourceLocatorWithSegmentKey()
    {
        $resourceLocator = 'path/to/document';
        $webspaceKey = 'sulu_io';
        $languageCode = 'de';
        $segmentKey = 'segment';

        $this->mapper->loadByResourceLocator($resourceLocator, $webspaceKey, $languageCode, $segmentKey)->willReturn(
            'uuid'
        );

        $result = $this->treeStrategy->loadByResourceLocator(
            $resourceLocator,
            $webspaceKey,
            $languageCode,
            $segmentKey
        );
        $this->assertEquals('uuid', $result);
    }

    public function testIsValid()
    {
        $path = 'som/valid/path';
        $this->cleaner->validate($path)->willReturn(true);

        $this->assertTrue($this->treeStrategy->isValid($path, 'default', 'de'));
    }

    public function testIsValidSlash()
    {
        $this->assertFalse($this->treeStrategy->isValid('/', 'default', 'de'));
    }

    public function testDeleteByPath()
    {
        $path = 'path/to/document';
        $languageCode = 'de';

        $this->mapper->deleteById($path, $languageCode, null)->shouldBeCalled();
        $this->treeStrategy->deleteById($path, $languageCode);
    }

    public function testDeleteByPathWithSegment()
    {
        $path = 'path/to/document';
        $languageCode = 'de';
        $segmentKey = 'segment';

        $this->mapper->deleteById($path, $languageCode, $segmentKey)->shouldBeCalled();
        $this->treeStrategy->deleteById($path, $languageCode, $segmentKey);
    }
}
