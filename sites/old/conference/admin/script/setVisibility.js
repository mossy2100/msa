function undisplayLayer(LAYEROBJ) {
   if (ns4) LAYEROBJ.display = 'none';
   else if (dyn) LAYEROBJ.style.display = 'none';
   }

function displayLayer(LAYEROBJ) {
   if (ns4) LAYEROBJ.display = 'block';
   else if (dyn) LAYEROBJ.style.display = 'block';
   }



function setVisibility( id, value )
{
	// uses functions from dynlib.js
	obj = getLayerObj( id );
	if (value) displayLayer( obj );
	else undisplayLayer( obj );
}