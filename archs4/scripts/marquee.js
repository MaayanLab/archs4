function listeners () {
  demo.jqContainer.mousedown(mouseDown);
  demo.jqContainer.mouseup(mouseUp);
  demo.jqContainer.mousemove(marqueeSelect);
  $(document).mousemove(resetMarquee);
}

function resetMarquee () {
  mouseup = true;
  mousedown = false;
  marquee.fadeOut();
  marquee.css({width: 0, height: 0});
  mousedowncoords = {};
}

function mouseDown (event) {

  event.preventDefault();

  var pos = {};

  mousedown = true;
  mousedowncoords = {};

  mousedowncoords.x = event.clientX;
  mousedowncoords.y = event.clientY;

  // adjust the mouse select
  pos.x = ((event.clientX - offset.x) / demo.jqContainer.width()) * 2 -1;
  pos.y = -((event.clientY - offset.y) / demo.jqContainer.height()) * 2 + 1;

  var vector = new THREE.Vector3(pos.x, pos.y, 1);

  demo.projector.unprojectVector(vector, demo.cameras.liveCam);

  // removing previous click marker.
  $(".clickMarkers").remove();

  // appending a click marker.
  demo.jqContainer.append('<div class="clickMarkers" style="pointer-events:none; position: absolute; z-index: 100; left: ' + event.offsetX + 'px; top: ' + event.offsetY +'px">D</div>' );

}

function mouseUp (event) {
  event.preventDefault();
  event.stopPropagation();

  // reset the marquee selection
  resetMarquee();

  // appending a click marker.
  demo.jqContainer.append('<div class="clickMarkers" style="left: ' + event.offsetX + 'px; top: ' + event.offsetY +'px">U</div>' );
}

function marqueeSelect (event) {
  event.preventDefault();
  event.stopPropagation();

  // make sure we are in a select mode.
  if(mousedown){

    marquee.fadeIn();

    var pos = {};
    pos.x = event.clientX - mousedowncoords.x;
    pos.y = event.clientY - mousedowncoords.y;

    // square variations
    // (0,0) origin is the TOP LEFT pixel of the canvas.
    //
    //  1 | 2
    // ---.---
    //  4 | 3
    // there are 4 ways a square can be gestured onto the screen.  the following detects these four variations
    // and creates/updates the CSS to draw the square on the screen
    if (pos.x < 0 && pos.y < 0) {
        marquee.css({left: event.clientX + 'px', width: -pos.x + 'px', top: event.clientY + 'px', height: -pos.y + 'px'});
    } else if ( pos.x >= 0 && pos.y <= 0) {
        marquee.css({left: mousedowncoords.x + 'px',width: pos.x + 'px', top: event.clientY, height: -pos.y + 'px'});
    } else if (pos.x >= 0 && pos.y >= 0) {
        marquee.css({left: mousedowncoords.x + 'px', width: pos.x + 'px', height: pos.y + 'px', top: mousedowncoords.y + 'px'});
    } else if (pos.x < 0 && pos.y >= 0) {
        marquee.css({left: event.clientX + 'px', width: -pos.x + 'px', height: pos.y + 'px', top: mousedowncoords.y + 'px'});
    }

    var selectedCubes = findCubesByVertices({x: event.clientX, y: event.clientY});
    demo.setup.highlight(selectedCubes);
  }
}

function findCubesByVertices(location){
  var currentMouse = {},
      mouseInitialDown = {},
      units,
      bounds,
      inside = false,
      selectedUnits = [],
      dupeCheck = {};

  currentMouse.x = location.x;
  currentMouse.y = location.y;

  mouseInitialDown.x = (mousedowncoords.x - offset.x);
  mouseInitialDown.y = (mousedowncoords.y - offset.y);

  units = pointGEO.points;
  bounds = findBounds(currentMouse, mousedowncoords);

  for(var i = 0; i < 10; i++) {
    inside = withinBounds(units[i].pos, bounds);
    console.log(units[i].pos);
    if(inside && (dupeCheck[units[i].id] === undefined)){
      selectedUnits.push(units[i]);
      dupeCheck[units[i].name] = true;
    }
  }
  return selectedUnits;
}

// takes the mouse up and mouse down positions and calculates an origin
// and delta for the square.
// this is compared to the unprojected XY centroids of the cubes.
function findBounds (pos1, pos2) {
    // calculating the origin and vector.
    var origin = {},
        delta = {};

    if (pos1.y < pos2.y) {
        origin.y = pos1.y;
        delta.y = pos2.y - pos1.y;
    } else {
        origin.y = pos2.y;
        delta.y = pos1.y - pos2.y;
    }

    if(pos1.x < pos2.x) {
        origin.x = pos1.x;
        delta.x = pos2.x - pos1.x;
    } else {
        origin.x = pos2.x;
        delta.x = pos1.x - pos2.x;
    }
    return ({origin: origin, delta: delta});
}

// Takes a position and detect if it is within delta of the origin defined by findBounds ({origin, delta})
function withinBounds(pos, bounds) {

    var ox = bounds.origin.x,
        dx = bounds.origin.x + bounds.delta.x,
        oy = bounds.origin.y,
        dy = bounds.origin.y + bounds.delta.y;

    if((pos.x >= ox) && (pos.x <= dx)) {
        if((pos.y >= oy) && (pos.y <= dy)) {
            return true;
        }
    }

    return false;
}

function getUnitVertCoordinates (threeJsContext) {

  var units = [],
      verts = [],
      child,
      prevChild,
      unit,
      vector,
      pos,
      temp,
      i, q;

  for(i = 0; i < demo.collisions.length; i++) {
      child = demo.collisions[i];
      child.updateMatrixWorld();

      verts = [
          child.geometry.vertices[0],
          child.geometry.vertices[1],
          child.geometry.vertices[2],
          child.geometry.vertices[3],
          child.geometry.vertices[4],
          child.geometry.vertices[5],
          child.geometry.vertices[6],
          child.geometry.vertices[7]
      ];

      for(q = 0; q < verts.length; q++) {
          vector = verts[q].clone();
          vector.applyMatrix4(child.matrixWorld);
          unit = {};
          unit.id = child.id;
          unit.mesh = child;
          unit.pos = toScreenXY(vector);;
          units.push(unit);
      }
  }
  return units;
}

function toScreenXY (position) {

  var pos = position.clone();
  var projScreenMat = new THREE.Matrix4();
  projScreenMat.multiplyMatrices( demo.cameras.liveCam.projectionMatrix, demo.cameras.liveCam.matrixWorldInverse );
  pos.applyProjection(projScreenMat);

  return { x: ( pos.x + 1 ) * demo.jqContainer.width() / 2 + demo.jqContainer.offset().left,
       y: ( - pos.y + 1) * demo.jqContainer.height() / 2 + demo.jqContainer.offset().top };
}



