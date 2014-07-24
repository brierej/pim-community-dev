<?php

namespace Pim\Bundle\DataGridBundle\Datagrid;

/**
 * Extract request parameters from Oro RequestParameters and fallback on Request, idea is to wrap
 * the use of RequestParameters which disappears in future Oro version
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RequestParametersExtractorInterface
{
    /**
     * Return the parameter
     *
     * @param string $key
     *
     * @return string
     */
    public function getParameter($key);
}