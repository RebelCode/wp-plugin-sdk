<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;
use Dhii\Services\Service;

/**
 * @psalm-import-type ServiceRef from Service
 */
class NoticeManager
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $nonceAction;
    /** @var string */
    protected $ajaxAction;
    /** @var Notice[] */
    protected $notices;

    /**
     * Constructor.
     */
    public function __construct(string $nonceAction, string $ajaxAction, array $notices = [])
    {
        $this->nonceAction = $nonceAction;
        $this->ajaxAction = $ajaxAction;
        $this->notices = [];

        foreach ($notices as $notice) {
            $this->add($notice);
        }
    }

    /** Creates an instance using just a prefix for the nonce and AJAX actions. */
    public static function create(string $prefix, array $notices = []): self
    {
        $dismissAction = "{$prefix}_dismiss_notice";

        return new self($dismissAction, $dismissAction, $notices);
    }

    /** Adds a notice to the manager. */
    public function add(Notice $notice)
    {
        $this->notices[$notice->id] = $notice;
    }

    /** Gets a notice by ID. */
    public function get(string $id): ?Notice
    {
        return $this->notices[$id] ?? null;
    }

    /** Shows a notice by ID. */
    public function show(string $id)
    {
        $notice = $this->notices[$id] ?? null;

        if ($notice !== null) {
            add_action('admin_notices', function () use ($notice) {
                echo $this->getScript($notice->id);
                echo $notice->render();
            });
        }
    }

    /** Dismisses a notice by ID. */
    public function dismiss(string $id)
    {
        $notice = $this->notices[$id] ?? null;

        if ($notice !== null) {
            $notice->dismiss();
        }
    }

    /** Hooks the notice manager into WordPress to handle AJAX requests. */
    public function listenForRequests()
    {
        add_action('wp_ajax_' . $this->ajaxAction, function () {
            $this->handleAjax($_POST);
            die;
        });
    }

    /** Handles a notice dismissal request */
    public function handleAjax(array $postRequest): bool
    {
        $id = filter_var($postRequest['notice'] ?? null);
        $nonce = filter_var($postRequest['nonce'] ?? null);

        if (wp_verify_nonce($nonce, $this->nonceAction)) {
            if (array_key_exists($id, $this->notices)) {
                $this->dismiss($id);
                return true;
            } else {
                status_header(400);
                echo __('Invalid notice ID');
                return false;
            }
        } else {
            status_header(400);
            echo __('Invalid nonce');
            return false;
        }
    }

    /** Retrieves the script for a notice with a given ID. */
    public function getScript(string $id): string
    {
        $jsTemplate = file_get_contents(__DIR__ . '/../../assets/js/notices.js');
        $jsCode = sprintf(
            $jsTemplate,
            $id,
            esc_attr(admin_url('admin-ajax.php')),
            esc_attr($this->ajaxAction),
            esc_attr(wp_create_nonce($this->nonceAction))
        );

        return sprintf('<script type="text/javascript">%s</script>', $jsCode);
    }

    /**
     * Creates a factory for a notice manager.
     *
     * @param ServiceRef $pluginCode The plugin code.
     * @param ServiceRef $notices The notices.
     */
    public static function factory($pluginCode, $notices): Factory
    {
        return new Factory([$pluginCode, $notices], function (string $code, array $notices) {
            return NoticeManager::create($code, $notices);
        });
    }
}
