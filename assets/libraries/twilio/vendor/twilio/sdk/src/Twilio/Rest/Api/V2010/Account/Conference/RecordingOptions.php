<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Api\V2010\Account\Conference;

use Twilio\Options;
use Twilio\Values;

abstract class RecordingOptions {
    /**
     * @param string $pauseBehavior Whether to record during a pause
     * @return UpdateRecordingOptions Options builder
     */
    public static function update(string $pauseBehavior = Values::NONE): UpdateRecordingOptions {
        return new UpdateRecordingOptions($pauseBehavior);
    }

    /**
     * @param string $dateCreatedBefore The `YYYY-MM-DD` value of the resources to
     *                                  read
     * @param string $dateCreated The `YYYY-MM-DD` value of the resources to read
     * @param string $dateCreatedAfter The `YYYY-MM-DD` value of the resources to
     *                                 read
     * @return ReadRecordingOptions Options builder
     */
    public static function read(string $dateCreatedBefore = Values::NONE, string $dateCreated = Values::NONE, string $dateCreatedAfter = Values::NONE): ReadRecordingOptions {
        return new ReadRecordingOptions($dateCreatedBefore, $dateCreated, $dateCreatedAfter);
    }
}

class UpdateRecordingOptions extends Options {
    /**
     * @param string $pauseBehavior Whether to record during a pause
     */
    public function __construct(string $pauseBehavior = Values::NONE) {
        $this->options['pauseBehavior'] = $pauseBehavior;
    }

    /**
     * Whether to record during a pause. Can be: `skip` or `silence` and the default is `silence`. `skip` does not record during the pause period, while `silence` will replace the actual audio of the call with silence during the pause period. This parameter only applies when setting `status` is set to `paused`.
     *
     * @param string $pauseBehavior Whether to record during a pause
     * @return $this Fluent Builder
     */
    public function setPauseBehavior(string $pauseBehavior): self {
        $this->options['pauseBehavior'] = $pauseBehavior;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string {
        $options = \http_build_query(Values::of($this->options), '', ' ');
        return '[Twilio.Api.V2010.UpdateRecordingOptions ' . $options . ']';
    }
}

class ReadRecordingOptions extends Options {
    /**
     * @param string $dateCreatedBefore The `YYYY-MM-DD` value of the resources to
     *                                  read
     * @param string $dateCreated The `YYYY-MM-DD` value of the resources to read
     * @param string $dateCreatedAfter The `YYYY-MM-DD` value of the resources to
     *                                 read
     */
    public function __construct(string $dateCreatedBefore = Values::NONE, string $dateCreated = Values::NONE, string $dateCreatedAfter = Values::NONE) {
        $this->options['dateCreatedBefore'] = $dateCreatedBefore;
        $this->options['dateCreated'] = $dateCreated;
        $this->options['dateCreatedAfter'] = $dateCreatedAfter;
    }

    /**
     * The `date_created` value, specified as `YYYY-MM-DD`, of the resources to read. You can also specify inequality: `DateCreated<=YYYY-MM-DD` will return recordings generated at or before midnight on a given date, and `DateCreated>=YYYY-MM-DD` returns recordings generated at or after midnight on a date.
     *
     * @param string $dateCreatedBefore The `YYYY-MM-DD` value of the resources to
     *                                  read
     * @return $this Fluent Builder
     */
    public function setDateCreatedBefore(string $dateCreatedBefore): self {
        $this->options['dateCreatedBefore'] = $dateCreatedBefore;
        return $this;
    }

    /**
     * The `date_created` value, specified as `YYYY-MM-DD`, of the resources to read. You can also specify inequality: `DateCreated<=YYYY-MM-DD` will return recordings generated at or before midnight on a given date, and `DateCreated>=YYYY-MM-DD` returns recordings generated at or after midnight on a date.
     *
     * @param string $dateCreated The `YYYY-MM-DD` value of the resources to read
     * @return $this Fluent Builder
     */
    public function setDateCreated(string $dateCreated): self {
        $this->options['dateCreated'] = $dateCreated;
        return $this;
    }

    /**
     * The `date_created` value, specified as `YYYY-MM-DD`, of the resources to read. You can also specify inequality: `DateCreated<=YYYY-MM-DD` will return recordings generated at or before midnight on a given date, and `DateCreated>=YYYY-MM-DD` returns recordings generated at or after midnight on a date.
     *
     * @param string $dateCreatedAfter The `YYYY-MM-DD` value of the resources to
     *                                 read
     * @return $this Fluent Builder
     */
    public function setDateCreatedAfter(string $dateCreatedAfter): self {
        $this->options['dateCreatedAfter'] = $dateCreatedAfter;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string {
        $options = \http_build_query(Values::of($this->options), '', ' ');
        return '[Twilio.Api.V2010.ReadRecordingOptions ' . $options . ']';
    }
}