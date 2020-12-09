<?php

declare(strict_types=1);

namespace Yiisoft\Profiler;

use Yiisoft\Strings\WildcardPattern;

trait MessageFilterTrait
{
    /**
     * @var array list of message categories that this target is interested in. Defaults to empty, meaning all
     * categories.
     *
     * You can use an asterisk at the end of a category so that the category may be used to
     * match those categories sharing the same common prefix. For example, 'Yiisoft\Db\*' will match
     * categories starting with 'Yiisoft\Db\', such as `Yiisoft\Db\Connection`.
     */
    public array $include = [];

    /**
     * @var array list of message categories that this target is NOT interested in. Defaults to empty, meaning no
     * uninteresting messages.
     *
     * If this property is not empty, then any category listed here will be excluded from {@see include}.
     * You can use an asterisk at the end of a category so that the category can be used to
     * match those categories sharing the same common prefix. For example, 'Yiisoft\Db\*' will match
     * categories starting with 'Yiisoft\Db\', such as `Yiisoft\Db\Connection`.
     *
     * {@see include}
     */
    public array $exclude = [];

    /**
     * Filters the given messages according to their categories.
     *
     * @param Message[] $messages messages to be filtered.
     * The message structure follows that in {@see Profiler::$messages}.
     *
     * @return array the filtered messages.
     */
    protected function filterMessages(array $messages): array
    {
        foreach ($messages as $i => $message) {
            if (!$this->isCategoryMatched($message->level())) {
                unset($messages[$i]);
            }
        }
        return $messages;
    }

    private function isCategoryMatched($category): bool
    {
        $matched = empty($this->include);

        foreach ($this->include as $pattern) {
            if ((new WildcardPattern($pattern))->match($category)) {
                $matched = true;
                break;
            }
        }

        if ($matched) {
            foreach ($this->exclude as $pattern) {
                if ((new WildcardPattern($pattern))->match($category)) {
                    $matched = false;
                    break;
                }
            }
        }
        return $matched;
    }
}
