<?php

/**
 * A collection of routes bound to aEntity objects. 
 * Uses the aEntityRoute class to ensure that links can be
 * generated when no explicit 'class' parameter is present.
 * This allows the admin generator to work with a 
 * :class parameter in the route prefix
 */

class aEntityRouteCollection extends sfObjectRouteCollection
{
  protected
    $routeClass = 'aEntityRoute';
}