<?php

namespace BrilliantMind\MPesa\Facades;

/**
 * Alias retrocompatível de {@see MPesa}.
 *
 * O namespace deste ficheiro era BrilliantMind\MPesa, o que violava a PSR-4 e
 * tornava a facade impossível de resolver.
 */
class MPesaFacade extends MPesa
{
}
