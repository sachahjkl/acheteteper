<?php

namespace Acheteteper;

class Timings
{
    private array $starts = [];
    private array $durationsSecs = [];
    public function __construct(
        public string $requestMethod = '',
        public string $requestPath = ''
    ) {}

    public function toArray(): array
    {
        return $this->durationsSecs;
    }

    public function toHtml(): string
    {
        $timings = $this->toArray();
        $sb = "";
        $sb .= '<div style="position:fixed;bottom:8px;right:8px;background:#111;color:#0f0;padding:8px 12px;font:12px monospace;z-index:9999;border:1px solid #0f0;line-height:1.4;opacity:0.9;">';
        $sb .= '<div style="margin-bottom:6px; color: #ff2300;"><b>WARNING : DEBUG MODE ENABLED</b></div>';
        $sb .= '<div style="margin-bottom:6px;"><b>Request:</b> ' . htmlspecialchars($this->requestMethod . ' ' . $this->requestPath, ENT_QUOTES, 'UTF-8') . '</div>';
        $sb .= '<b style="font-size:1.1em;">Timings:</b><br>';
        foreach ($timings as $name => $durationSecs) {
            $durationMs = $durationSecs * 1000;
            $sb .= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ': ' . number_format($durationMs, 4) . ' ms / ' . number_format($durationSecs, 4) . ' s<br>';
        }
        $sb .= '</div>';
        return $sb;
    }

    public function setRequestMeta(string $method, string $path): void
    {
        $this->requestMethod = $method;
        $this->requestPath = $path;
    }

    public function startMeasurement(string $name): void
    {
        $this->starts[$name] = microtime(true);
    }

    public function stopMeasurement(string $name): void
    {
        if (!isset($this->starts[$name])) {
            return;
        }
        $elapsedSecs = microtime(true) - $this->starts[$name];
        $this->durationsSecs[$name] = $elapsedSecs;
        unset($this->starts[$name]);
    }
}
