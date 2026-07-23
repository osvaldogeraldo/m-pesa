<?php

namespace BrilliantMind\MPesa\Facades;

/**
 * Backwards compatible alias for {@see MPesa}.
 *
 * The namespace of this file used to be BrilliantMind\MPesa, which broke PSR-4
 * autoloading and made the facade impossible to resolve.
 */
class MPesaFacade extends MPesa
{
}
