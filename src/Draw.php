<?php
/**
 *
 * Draw.php
 *
 *
 * 
 * @copyright Copyright (c) 2015 David J Eddy
 * @link http://davidjeddy.com
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace davidjeddy\leaflet\plugins\draw;

use dosamigos\leaflet\Plugin;
use yii\web\JsExpression;
use yii\helpers\Json;


/**
 * Draw adds the ability to place line, shapes, and markers to your leaflet maps
 * 
 * @author David J Eddy <me@davidjeddy.com>
 * @link http://www.davidjeddy.com/
 * @link https://github.com/davidjeddy
 * @package davidjeddy\leaflet\plugins\draw
 */
class Draw extends Plugin
{
    /**
     * @var string the name of the javascript variable that will hold the reference
     * to the map object.
     */
    public $map = 'map';
    /**
     * @var array the options for the underlying LeafLetJs JS component.
     * Please refer to the LeafLetJs api reference for possible
     * [options](http://leafletjs.com/reference.html).
     */
    public $options = null;
    /**
     * existing Geojson string 
     */
    public $existingGeojson = '';

    /* get/set methods */

    /**
     * Returns the plugin name
     * @return string
     */
    public function getPluginName()
    {

        return 'leaflet:draw';
    }

    /**
     * Returns the processed js options
     * @return array
     */
    public function getOptions()
    {
        return ($this->options
            ? json::encode($this->options)
            : '{}'
        );
    }

    /* non get/set methods */

    /**
     * Returns the javascript ready code for the object to render
     * @return \yii\web\JsExpression
     */
    public function encode()
    {
        //change control, add edit/delete preloaded, save action
        $js = "
            var editableLayers = new L.FeatureGroup();
            {$this->map}.addLayer(editableLayers);

            var drawnItems = new L.FeatureGroup();
            {$this->map}.addLayer(drawnItems);

            var drawControl = new L.Control.Draw({
            draw: {
                circle: false,
                circlemarker: false
            },
            edit: {
                featureGroup: drawnItems
            }});
            {$this->map}.addControl(drawControl);

            //draw existing geojson
            var geojsonLayer=null;
                geojsonLayer = L.geoJson({$this->existingGeojson});
                geojsonLayer.eachLayer(
                    function(l){
                        drawnItems.addLayer(l);
                });

            {$this->map}.on('draw:created', function (e) {

                var layer = e.layer,
                feature = layer.feature = layer.feature || {}; // Intialize layer.feature

                feature.type = feature.type || 'Feature'; // Intialize feature.type
                var props = feature.properties = feature.properties || {}; // Intialize feature.properties

                drawnItems.addLayer(layer);
            });
            //action for save button on the page
            if(document.getElementById('Save')!=null)
            document.getElementById('Save').onclick = function(e) {
                // Extract GeoJson from featureGroup
                var data = drawnItems.toGeoJSON();
                // Stringify the GeoJson
                var convertedData = JSON.stringify(data);
                document.getElementById('geojson').value=convertedData;
                
              };
        ";

        return new JsExpression($js);
    }

    /**
     * Registers plugin asset bundle
     * @param \yii\web\View $view
     * @return mixed
     * @codeCoverageIgnore
     */
    public function registerAssetBundle($view)
    {
        DrawAsset::register($view);
        return $this;
    }
}
