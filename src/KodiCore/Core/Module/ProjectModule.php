<?php

namespace KodiCore\Core\Module;


use KodiCore\Application;

class ProjectModule extends Module
{
    public function before(): void
    {

    }

    public function getControllerNamespace(): string
    {
        return Application::getEnv("controller_namespace");
    }

    public function getRoutes(): array
    {
        // Nothing to do here.
        return [];
    }
}