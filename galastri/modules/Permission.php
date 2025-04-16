<?php
namespace galastri\modules;

use galastri\core\config\Config;
use galastri\extensions\Exception;
use galastri\modules\Tools;

final class Permission
{
    private array $userPermissions = [];
    private array $expectedPermissions = [];
    private string $defaultFailMessage;
    private string $currentFailMessage;
    private bool $requireAll = false;

    public function __construct()
    {
        $configFailMessage = Config::get('permissionFailMessage');

        $this->defaultFailMessage = $configFailMessage;
        $this->currentFailMessage = $configFailMessage;
    }

    public function checkFor(array|string ...$permissions): self
    {
        $this->userPermissions = Tools::arrayFlatten($permissions);
        return $this;
    }

    public function at(array|string ...$permissions): self
    {
        $this->expectedPermissions = Tools::arrayFlatten($permissions);
        return $this;
    }

    public function requireAll(): self
    {
        $this->requireAll = true;
        return $this;
    }

    public function allowAny(): self
    {
        $this->requireAll = false;
        return $this;
    }

    public function validate(): void
    {
        $valid = false;

        if ($this->requireAll) {
            $missingPermissions = array_diff($this->expectedPermissions, $this->userPermissions);
        
            $valid = count($missingPermissions) === 0;
        } else {
            $matchingPermissions = array_intersect($this->expectedPermissions, $this->userPermissions);
        
            $valid = count($matchingPermissions) > 0;
        }

        if (!$valid) {
            throw new Exception(
                $this->currentFailMessage,
                'PERMISSION_DENIED'
            );
        }
    }

    public function setFailMessage(string $message, string ...$printf): self
    {
        $this->currentFailMessage = vsprintf($message, $printf);

        return $this;
    }

    public function reset(): self
    {
        $this->userPermissions = [];
        $this->expectedPermissions = [];
        $this->requireAll = false;
        $this->setFailMessage($this->defaultFailMessage);

        return $this;
    }
}
