<?php

namespace RebelCode\WpSdk\Tests\Di;

use bovigo\vfs\vfsStream;
use Dhii\Services\Factory;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Di\JsonFileService;
use PHPUnit\Framework\TestCase;

class JsonFileServiceTest extends TestCase
{
    public function testIsAFactory()
    {
        $this->assertInstanceOf(Factory::class, new JsonFileService('', ''));
    }

    public function testItCanReadJsonFile()
    {
        $data = (object) [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $vfs = vfsStream::setup('root', null, [
            'foo.json' => json_encode($data),
        ]);

        $filePath = $vfs->url() . '/foo.json';
        $fileService = 'file_service';
        $defaultService = 'default_service';

        $service = new JsonFileService($fileService, $defaultService);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive([$fileService], [$defaultService])
                  ->willReturnOnConsecutiveCalls($filePath, null);

        $result = $service($container);

        $this->assertEquals($data, $result);
    }

    public function testItCanReturnDefaultIfFileDoesNotExist()
    {
        $vfs = vfsStream::setup('root', null, [
            'foo.json' => '',
        ]);

        $filePath = $vfs->url() . '/bad_file_name.json';
        $default = 'default_value';

        $fileService = 'file_service';
        $defaultService = 'default_service';

        $service = new JsonFileService($fileService, $defaultService);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive([$fileService], [$defaultService])
                  ->willReturnOnConsecutiveCalls($filePath, $default);

        $result = $service($container);

        $this->assertEquals($default, $result);
    }

    public function testItCanReturnDefaultIfFileContentIsNotJson()
    {
        $vfs = vfsStream::setup('root', null, [
            'foo.json' => 'not JSON content',
        ]);

        $filePath = $vfs->url() . '/foo.json';
        $default = 'default_value';

        $fileService = 'file_service';
        $defaultService = 'default_service';

        $service = new JsonFileService($fileService, $defaultService);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive([$fileService], [$defaultService])
                  ->willReturnOnConsecutiveCalls($filePath, $default);

        $result = $service($container);

        $this->assertEquals($default, $result);
    }
}
