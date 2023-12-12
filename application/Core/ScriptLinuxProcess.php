<?php

namespace Aladser\Core;

/** PHP-скрипт как Linux процесс */
class ScriptLinuxProcess
{
    /** имя процесса */
    private string $processName;
    /** путь скрипта */
    private string $processFile;
    /** путь к логам процесса */
    private string $processLogFile;
    /** путь к файлу парсинга процессов Linux */
    private string $pidsParseFile;

    public function __construct(
        string $processName,
        string $scriptFilepath,
        string $processLogFilepath,
        string $pidParseFilepath
    ) {
        $this->processName = $processName;
        $this->processFile = $scriptFilepath;
        $this->processLogFile = $processLogFilepath;
        $this->pidsParseFile = $pidParseFilepath;
    }

    /** проверить наличие процесса */
    public function isActive(): bool
    {
        // новая таблица pidов
        exec("ps aux | grep {$this->processName} > $this->pidsParseFile");

        // 2 строки будут всегда
        return count(file($this->pidsParseFile)) > 2;
    }

    /** создает процесс */
    public function run(): void
    {
        exec("php $this->processFile > $this->processLogFile &");
    }

    /** убивает процесс */
    public function kill(): void
    {
        exec("pkill -f {$this->processName}");
    }
}
