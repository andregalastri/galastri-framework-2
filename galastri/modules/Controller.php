<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Controller
 */

namespace galastri\modules;

use galastri\core\Router;
use galastri\core\Galastri;
use galastri\core\config\Config;

abstract class Controller
{
    private bool $isConstructed = false;
    private array $controllerMethodData = [];
    private array $controllerHttpMethodData = [];
    private array $response = [];
    private array $fileContents = [];

    final public function __construct()
    {
        if (!$this->isConstructed) {
            
            // $this->callDoBefore();
            $this->callControllerMethod();
            $this->callHttpMethod();
            // $this->callDoAfter();
            $this->mergeReturns();

            $this->isConstructed = true;
        }
    }

    abstract protected function main(): array;

    private function callControllerMethod(): void
    {
        $controllerMethodName = Galastri::getControllerMethodName();

        $this->controllerMethodData = $this->$controllerMethodName();
    }

    private function callHttpMethod(): void
    {
        $controllerHttpMethodName = Galastri::getControllerHttpMethodName();

        if (Galastri::httpMethodExists()) {
            $this->controllerHttpMethodData = $this->$controllerHttpMethodName();
        }
    }

    private function mergeReturns(): void
    {
        $this->response = array_merge(
            $this->controllerMethodData,
            $this->controllerHttpMethodData,
            [
                'projectName' => Config::get('projectName'),
                'title' => Config::get('title'),
                'rootDir' => Router::getRootDir(),
            ]
        );
    }

    final protected function setProjectName(string $value): void
    {
        Config::set('projectName', $value);
    }

    final protected function getProjectName(): string
    {
        return Config::get('projectName');
    }

    /************** */

    final protected function setTitle(string $value): void
    {
        Config::set('title', $value);
    }

    final protected function getTitle(): string
    {
        return Config::get('title');
    }

    /************** */

    final protected function setOutput(string $value): void
    {
        Config::set('output', $value);
    }

    final protected function getOutput(): string
    {
        return Config::get('output');
    }

    /************** */

    final protected function setTimezone(string $value): void
    {
        Config::set('timezone', $value);
    }

    final protected function getTimezone(): string
    {
        return Config::get('timezone');
    }

    /************** */

    final protected function setTemplateFile(string $value): void
    {
        Config::set('templateFile', $value);
    }

    final protected function getTemplateFile(): string
    {
        return Config::get('templateFile');
    }

    /************** */

    final protected function setViewFolder(string $value): void
    {
        Config::set('viewFolder', $value);
    }

    final protected function getViewFolder(): string
    {
        return Config::get('viewFolder');
    }

    /************** */

    final protected function setFileFolder(string $value): void
    {
        Config::set('fileFolder', $value);
    }

    final protected function getFileFolder(): string
    {
        return Config::get('fileFolder');
    }

    /************** */

    final protected function setFileContents(string $contents, string $fileName, string $mimeType): void
    {
        $this->fileContents = [$contents, $fileName, $mimeType];
    }

    final public function getFileContents(): array
    {
        return $this->fileContents;
    }

    /************** */

    final protected function isDownloadableFile(bool|null $value = null): bool
    {
        if ($value == null) {
            return Config::get('isDownloadableFile');
        }

        Config::set('isDownloadableFile', $value);
    }
    
    /************** */
    
    final public function getResponse(): array
    {
        return $this->response;
    }

    /************** */

    final protected function getParameterValues(): array
    {
        return Route::getParameterValues();
    }

    final protected function getParameter(string $key): bool|null|string
    {
        return Route::getParameter($key) ?? false;
    }

    final protected function getDynamicNodeValues(): array
    {
        return Route::getDynamicNodeValues();
    }

    final protected function getDynamicNode(string $key): bool|null|string
    {
        return Route::getDynamicNode($key);
    }
}
