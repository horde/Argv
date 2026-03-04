<?php

declare(strict_types=1);

/**
 * Copyright 2024-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ralf Lang <lang@b1-systems.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Argv
 */

namespace Horde\Argv\Modern\Builder;

use Horde\Argv\Modern\Config\OptionConfig;
use Horde\Argv\Modern\Enum\{OptionAction, OptionType};

/**
 * Immutable builder for options.
 *
 * Following Principle #7: Immutable Throughout
 * Each method clones and returns new builder (~4μs overhead, negligible).
 *
 * Usage:
 *   $option = OptionBuilder::create()
 *       ->short('-v')
 *       ->long('--verbose')
 *       ->action(OptionAction::StoreTrue)
 *       ->help('Enable verbose output')
 *       ->build();
 *
 * @category Horde
 * @package  Argv
 */
class OptionBuilder
{
    private string $short = '';
    private string $long = '';
    private OptionAction $action = OptionAction::Store;
    private OptionType $type = OptionType::String;
    private string $dest = '';
    private mixed $default = null;
    private mixed $const = null;
    private ?string $help = null;
    private ?string $metavar = null;
    private array $choices = [];
    private int $nargs = 1;
    private mixed $callback = null;    // callable|null
    private mixed $validator = null;   // callable|null (Principle #3)
    private mixed $map = null;         // callable|null (Principle #3)

    private function __construct()
    {
    }

    /**
     * Create new builder instance.
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set short option (e.g., '-v').
     *
     * @param string $short Short option
     * @return self New builder instance
     */
    public function short(string $short): self
    {
        $new = clone $this;
        $new->short = $short;
        return $new;
    }

    /**
     * Set long option (e.g., '--verbose').
     *
     * @param string $long Long option
     * @return self New builder instance
     */
    public function long(string $long): self
    {
        $new = clone $this;
        $new->long = $long;
        return $new;
    }

    /**
     * Set action.
     *
     * @param OptionAction $action Action to perform
     * @return self New builder instance
     */
    public function action(OptionAction $action): self
    {
        $new = clone $this;
        $new->action = $action;
        return $new;
    }

    /**
     * Set type.
     *
     * @param OptionType $type Type validation/conversion
     * @return self New builder instance
     */
    public function type(OptionType $type): self
    {
        $new = clone $this;
        $new->type = $type;
        return $new;
    }

    /**
     * Set destination name.
     *
     * @param string $dest Destination name in result
     * @return self New builder instance
     */
    public function dest(string $dest): self
    {
        $new = clone $this;
        $new->dest = $dest;
        return $new;
    }

    /**
     * Set default value.
     *
     * @param mixed $default Default value
     * @return self New builder instance
     */
    public function default(mixed $default): self
    {
        $new = clone $this;
        $new->default = $default;
        return $new;
    }

    /**
     * Set constant value (for StoreConst/AppendConst).
     *
     * @param mixed $const Constant value
     * @return self New builder instance
     */
    public function const(mixed $const): self
    {
        $new = clone $this;
        $new->const = $const;
        return $new;
    }

    /**
     * Set help text.
     *
     * @param string $help Help text
     * @return self New builder instance
     */
    public function help(string $help): self
    {
        $new = clone $this;
        $new->help = $help;
        return $new;
    }

    /**
     * Set metavar (placeholder in help).
     *
     * @param string $metavar Metavar placeholder
     * @return self New builder instance
     */
    public function metavar(string $metavar): self
    {
        $new = clone $this;
        $new->metavar = $metavar;
        return $new;
    }

    /**
     * Set allowed choices.
     *
     * @param array $choices Allowed values
     * @return self New builder instance
     */
    public function choices(array $choices): self
    {
        $new = clone $this;
        $new->choices = $choices;
        return $new;
    }

    /**
     * Set number of arguments.
     *
     * @param int $nargs Number of arguments
     * @return self New builder instance
     */
    public function nargs(int $nargs): self
    {
        $new = clone $this;
        $new->nargs = $nargs;
        return $new;
    }

    /**
     * Set callback function (for Callback action).
     *
     * @param callable $callback Callback function
     * @return self New builder instance
     */
    public function callback(callable $callback): self
    {
        $new = clone $this;
        $new->callback = $callback;
        return $new;
    }

    /**
     * Add optional validator (Principle #3: Infrastructure Not Mandated).
     *
     * Validator should return true on success, or error string on failure.
     *
     * @param callable $validator Validator function
     * @return self New builder instance
     */
    public function validator(callable $validator): self
    {
        $new = clone $this;
        $new->validator = $validator;
        return $new;
    }

    /**
     * Add optional transformer (Principle #3: Infrastructure Not Mandated).
     *
     * Transformer receives validated value and returns transformed value.
     *
     * @param callable $map Transformer function
     * @return self New builder instance
     */
    public function map(callable $map): self
    {
        $new = clone $this;
        $new->map = $map;
        return $new;
    }

    // Convenience methods

    /**
     * Convenience: boolean flag (StoreTrue action).
     *
     * @return self New builder instance
     */
    public function flag(): self
    {
        return $this->action(OptionAction::StoreTrue);
    }

    /**
     * Convenience: repeatable option (Append action).
     *
     * @return self New builder instance
     */
    public function repeatable(): self
    {
        return $this->action(OptionAction::Append);
    }

    /**
     * Convenience: counter option (Count action).
     *
     * @return self New builder instance
     */
    public function counter(): self
    {
        return $this->action(OptionAction::Count);
    }

    /**
     * Build immutable config.
     *
     * @return OptionConfig Immutable option configuration
     */
    public function build(): OptionConfig
    {
        return new OptionConfig(
            short: $this->short,
            long: $this->long,
            action: $this->action,
            type: $this->type,
            dest: $this->dest,
            default: $this->default,
            const: $this->const,
            help: $this->help,
            metavar: $this->metavar,
            choices: $this->choices,
            nargs: $this->nargs,
            callback: $this->callback,
            validator: $this->validator,
            map: $this->map,
        );
    }
}
