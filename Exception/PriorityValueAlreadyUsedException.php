<?php

namespace Ae\WhiteLabelBundle\Exception;

class PriorityValueAlreadyUsedException extends \Exception
{

    /**
     * @param string $priority
     * @param string $website
     * @param string $alreadyUsedByWebsite
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($priority, $website, $alreadyUsedByWebsite, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'priority %s for website %s is already used by %s',
                $priority,
                $website,
                $alreadyUsedByWebsite
            ),
            $code,
            $previous
        );
    }
}
