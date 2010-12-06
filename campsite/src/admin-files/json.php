<?php
/**
 * @package Campsite
 *
 * @author Petr Jasek <petr.jasek@sourcefabric.org>
 * @copyright 2010 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl.txt
 * @link http://www.sourcefabric.org
 */

header('Content-type: application/json');

require_once WWW_DIR . '/classes/ServerRequest.php';

// include valid callbacks files
// TODO replace with Zend_Loader
require_once WWW_DIR . '/classes/Extension/WidgetManager.php';
require_once LIBS_DIR . '/ArticleList/ArticleList.php';
require_once LIBS_DIR . '/MediaList/MediaList.php';
require_once LIBS_DIR . '/ImageList/ImageList.php';
require_once WWW_DIR . '/classes/GeoNames.php';

try {
    // init request
    $serverRequest = new ServerRequest($_POST['callback'],
        (array) $_POST['args']);

    // set permissions
    $serverRequest->allow('ping');
    $serverRequest->allow('ArticleList::doAction'); // checked in handler
    $serverRequest->allow('ArticleList::doData');
    $serverRequest->allow('ArticleList::doOrder', 'Publish');
    $serverRequest->allow('WidgetManager::AddWidget');
    $serverRequest->allow('WidgetManagerDecorator::delete');
    $serverRequest->allow('WidgetRendererDecorator::render');
    $serverRequest->allow('WidgetManagerDecorator::getSetting');
    $serverRequest->allow('WidgetContext::setWidgets');
    $serverRequest->allow('WidgetManagerDecorator::update');
    $serverRequest->allow('Topic::UpdateOrder');
    $serverRequest->allow('Geo_Names::FindCitiesByLocation');
    $serverRequest->allow('Geo_Names::FindCitiesByName');
    $serverRequest->allow('MediaList::doData');
    $serverRequest->allow('ImageList::doData');

    // execute
    echo json_encode($serverRequest->execute());
} catch (Exception $e) {
    echo json_encode(array(
        'error_code' => $e->getCode(),
        'error_message' => getGS('Error') . ': ' . $e->getMessage(),
    ));
}

exit;

/**
 * Connection check function
 * @return bool
 */
function ping()
{
    return TRUE;
}
