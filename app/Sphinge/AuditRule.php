<?php

namespace App\Sphinge;

class AuditRule {

    /**
     * Title of the rule
     *
     * @var string
     */
    public $title;

    /**
     * Explaination of the rule
     *
     * @var string
     */
    public $info;

    /**
     * Result of the audit for this rule, should be one of "success" or "danger"
     *
     * @var string
     */
    public $status;

    public function __construct($title, $info, bool $status)
    {
        $this->title = $title;
        $this->info = $info;
        $this->status = $status ? 'success' : 'danger';
    }
}
