<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;
use Dhii\Services\Service;

/**
 * @psalm-import-type ServiceRef from Service
 */
class Notice
{
    public const INFO = 'info';
    public const ERROR = 'error';
    public const SUCCESS = 'success';
    public const WARNING = 'warning';
    public const NONE = '';

    /** @var string The notice type. See the constants in this class. */
    public $type;

    /** @var string The ID of the notice, used to identify the notice and also included in the notice HTML. */
    public $id;

    /** @var string The content of the notice. */
    public $content;

    /** @var bool Whether the notice can be dismissed. */
    public $isDismissible;

    /** @var AbstractOption|null The option to use to record if the notice has been dismissed. */
    public $option;

    /**
     * Constructor.
     *
     * @param string $type The notice type. See the constants in this class.
     * @param string $id The ID of the notice, used to identify the notice and also included in the notice HTML.
     * @param string $content The content of the notice.
     * @param bool $isDismissible Whether the notice can be dismissed.
     * @param AbstractOption|null $option The option to use to record if the notice has been dismissed.
     */
    public function __construct(
        string $type,
        string $id,
        string $content,
        bool $isDismissible = false,
        ?AbstractOption $option = null
    ) {
        $this->id = $id;
        $this->content = $content;
        $this->type = $type;
        $this->isDismissible = $isDismissible;
        $this->option = $option;
    }

    /** Renders the notice. */
    public function render(): string
    {
        $classes = ['notice'];

        if ($this->type !== self::NONE) {
            $classes[] = 'notice-' . $this->type;
        }

        if ($this->isDismissible) {
            $classes[] = 'is-dismissible';
        }

        return sprintf(
            '<div id="%s" class="%s">%s</div>',
            esc_attr($this->id),
            esc_attr(implode(' ', $classes)),
            wpautop($this->content)
        );
    }

    /** Returns a function that outputs the notice. */
    public function getEchoFn(): callable
    {
        return function () {
            echo $this->render();
        };
    }

    /** Dismisses the notice. */
    public function dismiss()
    {
        if ($this->isDismissible && $this->option) {
            $this->option->setValue(true);
        }
    }

    /**
     * Static constructor for an informational notice.
     *
     * @param string $id The ID of the notice, used to identify the notice and also included in the notice HTML.
     * @param string $content The content of the notice.
     * @param bool $isDismissible Whether the notice can be dismissed.
     */
    public static function info(
        string $id,
        string $content,
        bool $isDismissible = false,
        ?AbstractOption $option = null
    ): Notice {
        return new self(self::INFO, $id, $content, $isDismissible, $option);
    }

    /**
     * Static constructor for an error notice.
     *
     * @param string $id The ID of the notice, used to identify the notice and also included in the notice HTML.
     * @param string $content The content of the notice.
     * @param bool $isDismissible Whether the notice can be dismissed.
     */
    public static function error(
        string $id,
        string $content,
        bool $isDismissible = false,
        ?AbstractOption $option = null
    ): Notice {
        return new self(self::ERROR, $id, $content, $isDismissible, $option);
    }

    /**
     * Static constructor for a success notice.
     *
     * @param string $id The ID of the notice, used to identify the notice and also included in the notice HTML.
     * @param string $content The content of the notice.
     * @param bool $isDismissible Whether the notice can be dismissed.
     */
    public static function success(
        string $id,
        string $content,
        bool $isDismissible = false,
        ?AbstractOption $option = null
    ): Notice {
        return new self(self::SUCCESS, $id, $content, $isDismissible, $option);
    }

    /**
     * Static constructor for a warning notice.
     *
     * @param string $id The ID of the notice, used to identify the notice and also included in the notice HTML.
     * @param string $content The content of the notice.
     * @param bool $isDismissible Whether the notice can be dismissed.
     */
    public static function warning(
        string $id,
        string $content,
        bool $isDismissible = false,
        ?AbstractOption $option = null
    ): Notice {
        return new self(self::WARNING, $id, $content, $isDismissible, $option);
    }

    /**
     * Creates a factory for a notice, for use in modules.
     *
     * @param string $type The notice type. See the constants in this class.
     * @param string $id The ID of the notice, used to identify the notice and also included in the notice HTML.
     * @param string $content The content of the notice.
     * @param bool $isDismissible Whether the notice can be dismissed.
     * @param ServiceRef|null $option The service for the option to use to record if the notice has been dismissed.
     */
    public static function factory(
        string $type,
        string $id,
        string $content,
        bool $isDismissible = false,
        $option = null
    ): Factory {
        $deps = $option ? [$option] : [];

        return new Factory($deps, function ($option = null) use ($type, $id, $content, $isDismissible) {
            return new self($type, $id, $content, $isDismissible, $option);
        });
    }
}
