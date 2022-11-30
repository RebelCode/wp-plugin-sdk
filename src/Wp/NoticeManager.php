<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

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

    /** Handles a notice dismissal request */
    public function handleAjax(array $postRequest): bool
    {
        $id = filter_var($postRequest['notice'] ?? null, FILTER_DEFAULT);
        $nonce = filter_var($postRequest['nonce'] ?? null, FILTER_DEFAULT);

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

    /** Creates a factory for a notice manager. */
    public static function factory(string $pluginCodeService, string $noticesService): Factory
    {
        return new Factory([$pluginCodeService, $noticesService], function (string $code, array $notices) {
            return NoticeManager::create($code, $notices);
        });
    }
}
