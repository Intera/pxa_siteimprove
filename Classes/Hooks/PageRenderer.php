<?php
declare(strict_types=1);

namespace Pixelant\PxaSiteimprove\Hooks;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Class which adds the necessary resources for Siteimprove (https://siteimprove.com/).
 */
class PageRenderer implements SingletonInterface
{
    /**
     * Wrapper function called by hook (\TYPO3\CMS\Core\Page\PageRenderer->render-preProcess)
     *
     * @param array $parameters An array of available parameters
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer The parent object that triggered this hook
     */
    public function addResources(array $parameters, \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer)
    {
        // Add the resources only to the 'Page' module
        if (isset($GLOBALS['SOBE']) && get_class($GLOBALS['SOBE']) === PageLayoutController::class || is_subclass_of($GLOBALS['SOBE'],
                PageLayoutController::class)) {
            $domain = '';
            $pageId = (int)$GLOBALS['SOBE']->id;
            if ($pageId !== null) {
                $rootLine = BackendUtility::BEgetRootLine($pageId);
                $domain = BackendUtility::firstDomainRecord($rootLine);
            }

            $siteimproveOnDomReady = "
                $(document).ready(function() {
                    var _si = window._si || [];
                    $.ajax({
                            url: 'https://my2.siteimprove.com/auth/token?cms=TYPO3 8'
                        })
                        .done(function(data) {
                        if (data.token) {
                            _si.push(['domain', '" . $domain . "', data.token, function() { console.log('https://pixelant.se'); }])
                            }
                    });
                    // if (window._si !== undefined) { window._si.push(['showlog','']); }
                });";

            $pageRenderer->addJsFile('https://cdn.siteimprove.net/cms/overlay.js');
            $pageRenderer->addJsInlineCode('siteimproveOnDomReady', $siteimproveOnDomReady);
        }
    }
}
