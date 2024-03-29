<!DOCTYPE html>
<html lang="">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PolyForm</title>
    <style> 
    </style>
    <link rel="stylesheet" media="screen" href="defstylsh.css">
    <link rel="shortcut icon" type="image/jpg" href="painty.jpg">
    <script src="helper_functions.js"></script>
    <script src="https://d3js.org/d3.v4.min.js"></script>
  </head>
  <body>
      <div class="nav">
      <a href="index.php">Home</a>
      <a href="cards.php">Guessing Game</a>
      <a href="GUI-tar.php">GUI-tar</a>
      <a href="polyform.php" class="active">PolyForm</a>
      <a href="featured.php">Featured Arts</a>
      
      <div id='profile' style="float: right; text-align: right; padding: 10px;">
        <a href='register.php' style="position: absolute; right: 400px; padding: 0px;">Register</a>
        <form action = "php/authenticate.php" method="post">
          <input type="text" name="username" placeholder="Username" id="username" required>
          <input type="password" name="password" placeholder="Password" id="password" required>
          <input type="submit" value="Login">
        </form>
      </div>
      </div>
      <br>
      <section class="title-container">
            <h1 id="title">PolyForm</h1>
            <a href="https://www.twitch.tv/hammybonanza">Check out the development process live on twitch</a>
            <br>
            <br>
            <a href="https://www.patreon.com/ChristianBonin">Support me on Patreon!</a>
      </section>
      <br>
    <main>
      
      <div id='d3cont'></div>
      <div id='underbar'></div>
      <br>
      <div id='textfields'></div>
      <br>
      <div id='text_buttons'></div>
      
      <form id="upload">
      <label for="file">Background image to upload</label>
      <input type="file" id="file" accept="image/*">
      <button>Upload</button>
      </form>
      <div id='instructions'>
        <p style="text-decoration:underline;">Instructions:</p>
        <p>Click to plot points, press a key 0-9 to create a polygon from the points.</p>
        <p>Default colors: <br>1:Black 2:White 3:Pink 4:Red 5:Orange 6:Yellow 7:Green 8:Cyan 9:Blue 0:Purple</p>
        <p>Right click to select a polygon, selected polygons are highlighted yellow, 's' to select/deselect all, 'q' to remove selected</p>
        <p>Once selected, polygons can be transformed and recolored in the following ways:</p>
        <p>Arrow keys to move, &#60; &#62; keys to rotate, 'n' and 'm' keys to scale, 'x' and 'y' to mirror on x and y</p>
        <p>Raise and lower layer with [] keys, adjust transparency with 'o' and 'p' keys<p>
        <p>Press 'c' to copy the color your cursor is hovering over (from another shape or from background reference image) to selected polygons</p>
        <p>Buttons to load a background reference image (from web-link or from file) are located at bottom of app (MAY HAVE TO CLICK UPLOAD TWICE).</p>
        <p>'~' key will adjust the hue of selected polygons, 'v' and 'b' keys adjust brightness, numpad +- adjusts saturation (numpad *. to fully saturate or desat)</p>
        <p>Animations: 'f' key to toggle flash, 'g' toggles rotate, 'h' toggles hue cycling</p>
        <p>Press 'u' to undo last action, 'r' to redo last undo</p>
        <p>Press 'i' to hide all selected polygons or to unhide all hidden polygons if nothing is selected</p>
        <p>Press 'j' to join selected shapes into a single selectable object, 'k' to unjoin/separate joined pieces</p>
        <p>Click ENCODE to generate an encoded data string that can be used to reload the image in the editor, it will be output in the text area above the button<p>
        <p>Enter the encoded string into the same text area and click DECODE to load an image from its encoded string<p>
        <p>If you export the SVG as a file via the EXPORT SVG button, the encoded string can still be retreived by opening the svg in notepad or another text editor</p>
        <p>Edit speed controls how dramatic your changes are when you transform/recolor a polygon. Highlight width adjusts how big selected objects outlines are</p>
      </div>
      <footer>
      <div id="footer">
        Copyright 2022 Christian Bonin <a href="https://www.patreon.com/bePatron?u=72867978" data-patreon-widget-type="become-patron-button">Become a Patron!</a><script async src="https://c6.patreon.com/becomePatronButton.bundle.js"></script>
      </div>
    </footer>
    </main>

  </body>

  
<script>
    /*TODO
      Make button for adding background color
      Add new grow/shrink
      Add joining/unjoining to image encoding and/or add import svg button
      Resize image button
    */
    let screen_width  = screen.width;
    let width, height, c, ctx, x_y;
    let img = new Image();
    let edit_sp = 4;
    let stroke_width = 4;
    let pt_size = 2;

    let params = new URLSearchParams(window.location.search);
    if(params.has('width') && params.has('height'))
    {
      width = parseInt(params.get('width'));
      height = parseInt(params.get('height'));
    }
    else
    {
      width = Math.floor(screen_width/2.8);
      height = Math.floor(screen_width/2.8);
    }
    
    let last_click, last_right_click;
    let colors = ['black', 'white', 'pink', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple'];
    let shapes = [];
    let points = [];
    let point_elems = [];
    let selected = [];
    let hidden = [];
    let undo_history = [];
    let redo_history = [];
    let moused_over;
    let max_undo_steps = 40;
    let id_counter = 0;
    let last_operation = 0;
    let form = document.querySelector('#upload');
    let file = document.querySelector('#file');
    let img_pos = Math.floor(screen_width/2-width/2);

    d3.select('#instructions').attr('style', "position: absolute; top: 300px; height:1400px; width:".concat(img_pos-img_pos/3.6, "px; border:1px solid #600; font:16px overflow:auto; background-color:#DDD; padding:5px;"));

    //APPENDING ELEMENTS TO DOCUMENT
    let svg = d3.select("#d3cont")
                .append("svg")
                .attr("width", width)
                .attr("height", height)
                //center svg on page
                .attr("style", "position: absolute; left: ".concat(img_pos,"px;"));

    let border_n_grid = svg.append("g");
    let main_node = svg.append("g");
    main_node.attr("id","main_node");

    //paint background grid
    paintAlphaGrid(width,height);

    //make canvas for background images
    c = d3.select('#d3cont').append('canvas').attr('width',width).attr('height',height);
    document.getElementsByTagName('CANVAS')[0].style.position = 'absolute';
    document.getElementsByTagName('CANVAS')[0].style.left = "".concat(Math.floor(screen_width/2-width/2),'px');
    ctx = c.node().getContext('2d');
    c.lower();
    
    //exportSVG button
    d3.select("#underbar")
       .attr('style', 'transform: translate(0,'.concat(height+30,'px','); position: absolute; left: '.concat(img_pos,'px;')))
       .append("button")
       .html("EXPORT SVG")
       .on("click", exportSVG);
                         
    //add background reference image button
    d3.select("#underbar")
       .append("button")
       .html("LINK REF IMG")
       //true indicates its a web image
       .on("click", () => {let src = prompt("Enter path where image is hosted:","http://54.191.145.57/poly_stairs.jpg");
                           addbgRef(src, true);});

    //share button
    d3.select("#underbar")
      .append("button")
      .html("SHARE")
      .on("click", share);

    //encode button
    d3.select("#underbar")
      .attr("text-align","center")
      .attr("class","center")
      .append("button")
      .html("ENCODE")
      .on("click", () => document.getElementById("inputbox").value = encode());

    //decode button
    d3.select("#underbar")
      .attr("text-align","center")
      .attr("class","center")
      .append("button")
      .html("DECODE")
      .on("click", () => decode(document.getElementById("inputbox").value));

    //remove reference image button
    d3.select("#underbar")
      .attr("text-align","center")
      .attr("class","center")
      .append("button")
      .html("Remove BG")
      //null bg image will bring back grid
      .on("click", () => addbgRef(null));

   //contains all of the configurations that can be chosen by user
   let configs_form = d3.select("#underbar")
                        .append("form")
                        .attr('style', 'transform: translate(490px,-20px);');

   configs_form.append("label")
               .attr("for", "edit_sp")
               .html('Edit Speed:');

   edit_sp_dropdown = configs_form.append("select")
                                  .attr("id", "edit_sp");

   edit_sp_dropdown.append("option").html("0.5").on("click", () => edit_sp = 0.5);
   edit_sp_dropdown.append("option").html("1").on("click", () => edit_sp = 1);
   edit_sp_dropdown.append("option").html("2").on("click", () => edit_sp = 2);
   edit_sp_dropdown.append("option").attr('selected',true).html("4").on("click", () => edit_sp = 4);
   edit_sp_dropdown.append("option").html("8").on("click", () => edit_sp = 8);
   edit_sp_dropdown.append("option").html("16").on("click", () => edit_sp = 16);

   configs_form.append("label")
               .attr("for", "highlight_width")
               .html('highlight width:')
               .attr('style', 'padding: 5px;');

   highlight_dropdown = configs_form.append("select")
                                    .attr("id", "highlight_width");

   highlight_dropdown.append("option").html("0.5").on("click", () => changeStrokeWidth(0.5));
   highlight_dropdown.append("option").html("1").on("click", () => changeStrokeWidth(1));
   highlight_dropdown.append("option").html("2").on("click", () => changeStrokeWidth(2));
   highlight_dropdown.append("option").attr('selected',true).html("4").on("click", () => changeStrokeWidth(4));
   
   configs_form.append("label")
               .attr("for", "pt_size")
               .html('point size:')
               .attr('style', 'padding: 5px;');
   
   pt_size_dropdown = configs_form.append("select")
                                  .attr("id", "pt_size");

   pt_size_dropdown.append("option").html("0.5").on("click", () => changePtSize(0.5));
   pt_size_dropdown.append("option").html("1").on("click", () => changePtSize(1));
   pt_size_dropdown.append("option").attr('selected',true).html("2").on("click", () => changePtSize(2));
   pt_size_dropdown.append("option").html("4").on("click", () => changePtSize(4));

   if(params.has('data')) decode(params.get('data'));

   //textbox for our encodes and decodes
   let textbox = d3.select('#textfields')
                   .attr('style', 'transform: translate(0,'.concat(height+50,'px','); position: absolute; left: '.concat(img_pos,'px;')))
                   .append('input')
                   .attr('value', "Load encoded images or retrive output encoded data here.")
                   .attr('size', width/6)
                   .attr('style', 'font-size: 18px')
                   .attr('id', 'inputbox');

   //position our upload bg image from file section
   d3.select('#upload').attr('style', 'transform: translate(0,'.concat(height+70,'px','); position: absolute; left: '.concat(img_pos,'px;')));

   //EVENT BINDINGS
   svg.on("click", function(){

    last_click = d3.mouse(this);
    //avoid accidental double clicks
    if(last_click)
    {
      let click_to_op = parseInt("".concat(Math.floor(last_click[0]),Math.floor(last_click[1])))*-1+0.5;
      if(last_operation == click_to_op) return;
      last_operation = click_to_op;
    } 

    undo_history.push(undoElements());
    
    point_elems.push(main_node.append("circle")
                              .attr("cx", last_click[0])
                              .attr("cy", last_click[1])
                              .attr("r", pt_size)
                              .attr("fill", "black")
                              .attr("stroke-width", 1)
                              .attr("stroke", "white"));

    points.push(last_click);
    });

    //right click
    svg.on("contextmenu", function(){
              d3.event.preventDefault();
        });

    //keeps track of our mouse position whenever hovering over svg we are editing
    svg.on('mousemove', function(){x_y = d3.mouse(this);});

    //called by our config dropdowns
    function changeStrokeWidth(new_width)
    {
      for(sel of selected) d3.select(sel).attr('stroke-width', new_width);
      stroke_width = new_width;
    };

    function changePtSize(new_size)
    {
      for(pt of point_elems) pt.attr("r", new_size);
      pt_size = new_size;
    };

    function getAllChildPolys(group, children = [])
    {
      let currChildren = group.children;
      for(let i = 0; i < currChildren.length; i++)
      {
        if(currChildren[i].nodeName == 'g') getAllChildPolys(currChildren[i], children);
        else children.push(currChildren[i]);
      }
      return children;
    };

    //called on right click, selects topmost polygon under cursor
    function contextMenuCalled()
    {
      last_right_click = d3.mouse(this);
      //avoid accidental double clicks
      let click_to_op = parseInt("".concat(last_right_click[0],last_right_click[1]))*-1;
      if(last_operation == click_to_op) return;
      last_operation = click_to_op;
      
      undo_history.push(undoElements());
      while(undo_history.length > max_undo_steps) undo_history.shift();
      if(this.parentNode == main_node.node())
      {
        let index = selected.indexOf(this);

        if(index == -1)
        {
          selected.push(this);
          d3.select(this).attr('stroke-width', stroke_width)
                         .attr('stroke', "gold");
        }
        else
        {
          selected.splice(index,1);
          d3.select(this).attr('stroke-width', 0);
        }
      }
      else
      {
        let parent = this.parentNode;
        while(parent.parentNode != main_node.node()) parent = parent.parentNode;
        let index = selected.indexOf(parent);
        let children = getAllChildPolys(parent);

        if(index == -1)
        {
          selected.push(parent);
          for(let i = 0; i < children.length; i++)
          {
            d3.select(children[i]).attr('stroke-width', stroke_width)
                           .attr('stroke', "gold");
          }
        }
        else
        {
          selected.splice(index,1);
          for(let i = 0; i < children.length; i++)
          {
            d3.select(children[i]).attr('stroke-width', 0);
          }
        }
      }
    };

    //called whenever a key is pressed
    document.onkeydown = checkKey;

    //called when we press upload for background image file
    form.addEventListener('submit', function(event)
                                    {
                                      //stop page from reloading
                                      event.preventDefault();
                                      if(!file.value.length) return;
                                      let reader = new FileReader();
                                      reader.onload = function(event){
                                      addbgRef(event.target.result);
                                      }
                                      reader.readAsDataURL(file.files[0]);
                                    });

    //encode entire svg into a hex str, called when encode pressed, result stored in textbox
    //also called in exportSVG and stored inside SVG XML (can be retreived by opening SVG in text editor)
    function encode()
    {
      integerizePoints();
      let first_iter = true;
      let encodedStr = "";
      let pt_list, rgb, op_str, animId, originStr, origin;
      for(shape of shapes)
      {
        shape.attr('fill', d3.rgb(shape.attr('fill')).toString());
        if(first_iter) first_iter = false;
        else encodedStr = encodedStr.concat("A");
        pt_list = shape.node().points;
        encodedStr = encodedStr.concat(pt_list[0].x,'B',pt_list[0].y);
        for(let i = 1; i < pt_list.length; i++) encodedStr = encodedStr.concat('B',pt_list[i].x,'B',pt_list[i].y);
        encodedStr = encodedStr.concat('C');
        rgb = shape.attr('fill');
        rgb = rgb.slice(rgb.indexOf("(")+1,rgb.indexOf(')')).split(/[ ,]+/);
        encodedStr = encodedStr.concat(rgb[0], 'B', rgb[1], 'B', rgb[2]);

        animId = shape.attr('animId');
        if(animId) animId = parseInt(animId);
        if(shape.attr('opacity') || animId)
        {
          encodedStr = encodedStr.concat('C');
          if(!shape.attr('opacity') || parseInt(shape.attr('opacity')) == 1) encodedStr = encodedStr.concat("-1");
          else 
          {
            op_str = shape.attr('opacity');
            encodedStr = encodedStr.concat(op_str.slice(op_str.indexOf('.')+1));
          }

          if(animId)
          {
            encodedStr = encodedStr.concat('C', animId);
            //means we have a rotate, need to get origin
            if(animId >= 4)
            {
              encodedStr = encodedStr.concat('C');
              originStr = shape.select('animateTransform').node().getAttribute("from");
              origin = originStr.slice(originStr.indexOf(" ")+1).split(' ');
              encodedStr = encodedStr.concat(parseInt(origin[0]), "B", parseInt(origin[1]));
            }
          }
          
        }
      }
      encodedStr = encodedStr.replaceAll('-', 'D');
      return encodedStr;
    };

    //called when decode is pressed, loads code string from textbox into the image it represents in the svg editor
    function decode(encodedStr)
    {
      let enc_shapes = encodedStr.split('A');
      let attributes, points, fill, opacity, pt_str, fill_str, op_str, animId, origin;
      let i;
      for(i = 0; i < enc_shapes.length; i++)
      {
        //split our encoded string into easily parsable arrays
        attributes = enc_shapes[i].split('C');
        points = attributes[0].split('B');
        fill = attributes[1].split('B');
        //as of right now we are only looking at points, fill, and opacity attributes, opacity optional
        if(attributes.length > 2) opacity = attributes[2];
        if(attributes.length > 3) animId = attributes[3];

        //generate strings for our attributes
        pt_str = "";
        //stop before last x,y pair so we can add it without a space at the end
        let j;
        for(j = 0; j < points.length-2; j+=2) pt_str += points[j] + "," + points[j+1] + " ";
        pt_str += points[j] + "," + points[j+1];
        //replace all Es with negative signs
        pt_str = pt_str.replaceAll('D', '-');
        fill_str = "rgb(" + fill[0] + ", " + fill[1] + ", " + fill[2] +")";

        if(attributes.length > 2 && opacity != 'D1') op_str = "0." + opacity;
        else op_str = null;
        //create the shape and give it our attributes
        shapes.push(main_node.append("polygon")
                               .attr("points", pt_str)
                               .attr("fill", fill_str)
                               .attr('stroke-width', stroke_width)
                               .attr('stroke', 'gold')
                               .on("contextmenu", contextMenuCalled)
                               .on("mouseover", function(){moused_over = this;})
                               .on("mouseleave", function(){if(moused_over == this) moused_over = null;})
                               .attr('id', ''.concat(id_counter)));
        selected.push(shapes[shapes.length-1].node());
                               
        id_counter++;

        //if we have an opacity, apply it
        if(op_str) shapes[shapes.length-1].attr('opacity', op_str);
        if(attributes.length > 3 && animId != '0')
        {
          animId = parseInt(animId);
          if(animId%2 == 1) animHueElem(shapes[shapes.length-1]);
          animId = animId >> 1;
          if(animId%2 == 1) animFlashElem(shapes[shapes.length-1]);
          animId = animId >> 1;
          if(animId%2 == 1) {
            origin = attributes[4].split('B');
            animRotateElem(shapes[shapes.length-1],[parseInt(origin[0]),parseInt(origin[1])]);
          }
        }
      }

    };

    //called when share is pressed, generates a sharable weblink to image in editor if image is simple enough (URL length constraints)
    function share()
    {
      let shareStr = "http://54.191.145.57/polyform.html?width=".concat(width,"&height=",height,"&data=");
      shareStr += encode();
      if(shareStr.length < 8192)
      {
        alert(shareStr);
      }
      else alert("URL too large to share, try saving the SVG via EXPORT button and share the file another way");
    };

    //paints checkered grid thats on by default behind the elements in our SVG editor
    function paintAlphaGrid(width, height)
    {
      let grid_tile_size = Math.floor(width/20);
      let g_rows = Math.floor(width/grid_tile_size);
      let g_cols = Math.floor(height/grid_tile_size);

      if (width%grid_tile_size != 0) g_rows++;
      if (g_cols%grid_tile_size != 0) g_cols++;

      for(let r = 0; r < g_rows; r++)
      {
        for(let c = 0; c < g_cols; c++)
        {
          if(r%2 ==0)
          {
            if(c%2 == 0)
            {
              border_n_grid.append("rect")
                           .attr("x",r*grid_tile_size)                        
                           .attr("y",c*grid_tile_size)
                           .attr("width",grid_tile_size)
                           .attr("height",grid_tile_size)
                           .attr("fill", "gainsboro")
                           .attr("class", "alpha");
            }
            else
            {
              border_n_grid.append("rect")
                           .attr("x",r*grid_tile_size)                        
                           .attr("y",c*grid_tile_size)
                           .attr("width",grid_tile_size)
                           .attr("height",grid_tile_size)
                           .attr("fill", "white")
                           .attr("class", "alpha");
            }
          }
          else
          {
            if(c%2 == 1)
            {
              border_n_grid.append("rect")
                           .attr("x",r*grid_tile_size)                        
                           .attr("y",c*grid_tile_size)
                           .attr("width",grid_tile_size)
                           .attr("height",grid_tile_size)
                           .attr("fill", "gainsboro")
                           .attr("class", "alpha");
            }
            else
            {
              border_n_grid.append("rect")
                           .attr("x",r*grid_tile_size)                        
                           .attr("y",c*grid_tile_size)
                           .attr("width",grid_tile_size)
                           .attr("height",grid_tile_size)
                           .attr("fill", "white")
                           .attr("class", "alpha");
            }
          }
        }
      }

      border_n_grid.append("rect")
                   .attr("width", width)
                   .attr("height", height)
                   .attr("fill", "none")
                   .attr("stroke", "black")
                   .attr("stroke-width", 4);
    };

    //removes checkered grid thats on by default
    function removeGrid()
    {
      border_n_grid.selectAll('.alpha').remove();
    };

    //fits max amount of background image possible into our editor while maintaining aspect ratio
    //second argument should be passed in as true if src is a weblink, stay false or unincluded if src is base64
    function makeBGImage(src, web = false)
    {
      img.src = src;
      if(web) img.crossOrigin = '';

      let aspect;
      img.onload = function()
                  {
                    aspect = this.width/this.height;
                    if(aspect >= 1) ctx.drawImage(img, 0, 0, width, height/aspect);
                    else ctx.drawImage(img, 0, 0, width*aspect, height);
                    //print error if image fails to load, offer to open imgur in new tab
                    if(this.width == 0 && this.height == 0) 
                    {
                      
                      
                    } else error_str = null;
                  };

      img.onerror = function()
                    {
                      paintAlphaGrid(width,height);
                      img = new Image();
                      let error_str = "Image failed to load, if its a web-hosted image likely image owner"
                              .concat("has cross-origin disabled, try downloading the image and uploading via the")
                              .concat("to upload' section below. Or, alternatively, get a reference")
                              .concat("image from a cross-origin friendly source such as imgur.com")
                              .concat("\n Press ok to open imgur in new tab");
                      if(window.confirm(error_str)) window.open('https://imgur.com/', "_blank");

                    };
    };

    //paints a background image behind svg editor in place of default grid
    function addbgRef(src = null, web = false)
    {
      ctx.clearRect(0, 0, width, height);
      if(src == null)
      {
        img = new Image();
        paintAlphaGrid(width,height);
        return;
      } else removeGrid();
      
      return makeBGImage(src, web);
    };

    //creates a deep copy of everything currently in editor so we can recreate it
    function undoElements()
    {
      let undo_elems = [[],[],[...points]];
      let attributes;
      let dom_order = main_node.node().children;
      for(let j = 0; j < dom_order.length; j++)
      {
        if(dom_order[j].nodeName == 'circle') continue;
        let sel_elem = d3.select(dom_order[j]);
        undo_elems[0].push(dom_order[j].cloneNode(true));
      }

      for(sel of selected) undo_elems[1].push(sel.id);
      return undo_elems;
    };

    //reverts to last state we pushed to undo_history
    function undo()
    {
      if(undo_history.length)
      {
        let revert = undo_history.pop();
        redo_history.push(undoElements());
        while(redo_history.length > max_undo_steps) redo_history.shift();

        main_node.node().innerHTML = '';
        shapes = [];
        point_elems = [];

        points = revert[2];
        
        for(let i = 0; i < revert[0].length; i++)
        {
          main_node.node().appendChild(revert[0][i])
          if(revert[0][i].nodeName == 'g')
          {
            let children = getAllChildPolys(revert[0][i]);
            for(child of children) shapes.push(d3.select(child).on("contextmenu", contextMenuCalled)
                                                               .on("mouseover", function(){moused_over = this})
                                                               .on("mouseleave", function(){if(moused_over == this) moused_over = null;}));
          }
          else if(revert[0][i].nodeName == 'polygon') shapes.push(d3.select(revert[0][i]).on("contextmenu", contextMenuCalled)
                                                                                         .on("mouseover", function(){moused_over = this})
                                                                                         .on("mouseleave", function(){if(moused_over == this) moused_over = null;}));
        };

        selected = [];
        
        for(let i = 0; i < points.length; i++)
        {
          point_elems.push(main_node.append("circle")
                                .attr("cx", points[i][0])
                                .attr("cy", points[i][1])
                                .attr("r", pt_size)
                                .attr("fill", "black")
                                .attr("stroke-width", 1)
                                .attr("stroke", "white"));
        }

        for(let i = 0; i < revert[1].length; i++)
          for(let j = 0; j < revert[0].length; j++)
            if(revert[0][j].id == revert[1][i]) selected.push(revert[0][j]);

        return true;
      }
      return false;
    };

    //reverts to last state we pushed to redo_history
    function redo()
    {
      if(redo_history.length)
      {
        let revert = redo_history.pop();
        undo_history.push(undoElements());
        while(undo_history.length > max_undo_steps) undo_history.shift();

        main_node.node().innerHTML = '';
        shapes = [];
        point_elems = [];

        points = revert[2];

        for(let i = 0; i < revert[0].length; i++)
        {
          main_node.node().appendChild(revert[0][i])
          if(revert[0][i].nodeName == 'g')
          {
            let children = getAllChildPolys(revert[0][i]);
            for(child of children) shapes.push(d3.select(child).on("contextmenu", contextMenuCalled)
                                                               .on("mouseover", function(){moused_over = this})
                                                               .on("mouseleave", function(){if(moused_over == this) moused_over = null;}));
          }
          else if(revert[0][i].nodeName == 'polygon') shapes.push(d3.select(revert[0][i]).on("contextmenu", contextMenuCalled)
                                                                                         .on("mouseover", function(){moused_over = this})
                                                                                         .on("mouseleave", function(){if(moused_over == this) moused_over = null;}));
        };
        selected = [];
        
        for(let i = 0; i < points.length; i++)
        {
          point_elems.push(main_node.append("circle")
                                .attr("cx", points[i][0])
                                .attr("cy", points[i][1])
                                .attr("r", pt_size)
                                .attr("fill", "black")
                                .attr("stroke-width", 1)
                                .attr("stroke", "white"));
        }

        for(let i = 0; i < revert[1].length; i++)
          for(let j = 0; j < revert[0].length; j++)
            if(revert[0][j].id == revert[1][i]) selected.push(revert[0][j]);

        return true;
      }
      return false;
    };

    //duplicates element
    function cloneElem(elem)
    {
      if(elem.nodeName == 'polygon')
      {
        shapes.push(d3.select(elem).clone(true));
        shapes[shapes.length-1].attr('stroke-width', 0)
                               .on("contextmenu", contextMenuCalled)
                               .attr('id', ''.concat(id_counter))
                               .on("mouseover", function(){moused_over = this})
                               .on("mouseleave", function(){if(moused_over == this) moused_over = null;});
        id_counter++;
      }
      else
      {
         let join_group = main_node.append('g')
                              .attr('id', id_counter);
         let join_elem = join_group.node();
         id_counter++;
         let sel_child;
         for(poly of getAllChildPolys(elem)) join_elem.appendChild(poly.cloneNode(true));
         for(child of join_elem.children) 
         {
          shapes.push(d3.select(child).attr('stroke-width', 0)
                                      .on("contextmenu", contextMenuCalled)
                                      .attr('id', ''.concat(id_counter))
                                      .on("mouseover", function(){moused_over = this})
                                      .on("mouseleave", function(){if(moused_over == this) moused_over = null;}));
          id_counter++;
         }
       }
    };

  //select/deselect all, called when 'a' is pressed
  function selectAll()
  {
    let nothingSelected = selected.length == 0;
    //if nothing selected select all
    if(nothingSelected)
    {
      let children = main_node.node().children;
      let polychildren = getAllChildPolys(main_node.node());
      for(child of children) selected.push(child);
      for(child of polychildren)
      {
        let sel_child = d3.select(child);
        if(sel_child.attr('style') != 'visibility: hidden;')
        {
          
          sel_child.attr('stroke-width', stroke_width)
                   .attr('stroke', "gold");
        }
      }
    }
    //if anything selected deselect all
    else
    {
      for(let i = 0; i < shapes.length; i++)
      {
        shapes[i].attr('stroke-width', 0);
      }
      selected = [];
    }
  };

  //truncates pts of all shapes in svg canvas to integers to prep for exporting
  function integerizePoints()
  {
    let pt_list, pt_str;
    for(shape of shapes)
    {
      pt_list = shape.node().points;
      pt_str = "".concat(Math.floor(pt_list[0].x), ",", Math.floor(pt_list[0].y));
      for(let i = 1; i < pt_list.length; i++) pt_str = pt_str.concat(" ", Math.floor(pt_list[i].x), ",", Math.floor(pt_list[i].y));
      shape.attr('points', pt_str);
    }
  };

  //downloads contents of svg editor to a svg file for user
  function exportSVG()
  {
    integerizePoints();
    if(selected.length != 0) selectAll();
    let toExport = main_node.node();
    let groupStr;
    if(window.ActiveXObject)
    {
      groupStr = toExport.xml;
    }
    else
    {
      let oSerializer = new XMLSerializer();
      groupStr = oSerializer.serializeToString(toExport);
      groupStr = encodeURIComponent(groupStr);
    }

    let svgBegin = "data:image/svg+xml;utf8,%3Csvg%20width%3D%22".concat(width,"%22%20height%3D%22",height,"%22%3E%20id%3D%22",encode(),"%22%20");
    let svgEnd = "%3C%2Fsvg%3E";
    let svgString = svgBegin + groupStr + svgEnd;
    //creating link element but not appending it to document
    let link = document.createElement('a');
    let fileName = prompt("Save as:","svg");
    link.download = fileName;
    link.href = svgString;
    //remove svg styling xml tag from group
    let xmlnsTag = "xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20";
    link.href = removeFromString(link.href, xmlnsTag);
    //then add it to svg element instead
    let svgTag = "%3Csvg%20";
    let insertIndex = link.href.indexOf(svgTag)+svgTag.length;
    link.href = stringInsert(link.href, xmlnsTag, insertIndex);
    //will behave as if user clicked created link element (initiating download)
    link.click();
  };

  //remove a selected element
  function removeElem(element)
  {
    d3.select(element).remove();
    if(element.nodeName == 'polygon')
    {
      let elem = element;
      for(let i = 0; i < shapes.length; i++)
      {
        if(elem == shapes[i].node()) return shapes.splice(i,1);
      }
    }
    else
    {
      for(elem of getAllChildPolys(element))
      {
        for(let i = 0; i < shapes.length; i++)
        {
          if(elem == shapes[i].node()) shapes.splice(i,1);
        }
      }
    }
    return null;
  }

  //rotates a given polygon element a given number of degrees around given origin
  function applyRotation(elem, degrees, origin)
  {
    let pt_list, pt_str;
    let dX, dY, newX, newY, r, angle;
    rotation = [degrees, origin[0], origin[1]];
    pt_list = elem.points;
    pt_str = "";
    for(let j = 0; j < pt_list.length; j++)
    {
      dX = pt_list[j].x - rotation[1];
      dY = rotation[2] - pt_list[j].y;
      r = Math.sqrt(dX**2 + dY**2);
      angle = Math.atan2(dY,dX) - rotation[0]*Math.PI/180;
      newX = rotation[1] + r*Math.cos(angle);
      newY = rotation[2] - r*Math.sin(angle);
      if(j < pt_list.length-1)
      {
        pt_str = pt_str.concat(newX,",");
        pt_str = pt_str.concat(newY," ");
      }
    }
    //append last pt without trailing space
    pt_str = pt_str.concat(newX,",",newY);
    d3.select(elem).attr("points", pt_str);   
  };

  function getAllSelectedPolys()
  {
    let allSelectedPolys = [];
    for(sel of selected)
    {
      if(sel.nodeName == 'polygon') allSelectedPolys.push(sel);
      else allSelectedPolys.push(...getAllChildPolys(sel));
    }
    return allSelectedPolys;
  };

  //averages all points of all selected polygons to get the centerpoint of our entire selection
  function getSelectedCenter()
  {
    let xSum = 0;
    let ySum = 0;
    let num_values = 0;
    
    selectedPolys = getAllSelectedPolys();

    for(sel of selectedPolys)
    {
      pt_list = sel.points;
      for(pt of pt_list)
      {
        xSum += pt.x;
        ySum += pt.y;
        num_values++;
      }
    }
    return [xSum/num_values, ySum/num_values];
  };

  //scales a given polygon element by a [factorX,factorY] scale toward a given origin point (usually getSelectedCenter())
  function applyScale(elem, scale, origin)
  {
    let pt_list, pt_str;
    let x,y;
    pt_str = "";
    pt_list = elem.points;
    for(let j = 0; j < pt_list.length; j++)
    {
      x = (pt_list[j].x-origin[0])*scale[0];
      y = (pt_list[j].y-origin[1])*scale[1];

      if(j < pt_list.length-1)
      {
        pt_str = pt_str.concat(origin[0]+x,",");
        pt_str = pt_str.concat(origin[1]+y," ");
      }   
    }
    pt_str = pt_str.concat(origin[0]+x,",");
    pt_str = pt_str.concat(origin[1]+y);
    d3.select(elem).attr("points", pt_str);
  }

  //translates a given element a translation t, t = [xTranslation, yTranslation]
  function applyTranslation(elem, t)
  {
    if(t[0] == 0 && t[1] == 0) return;

    let pt_list = elem.points;

    let pt_str = "";

    let j;
    for(j = 0; j < pt_list.length-1; j++)
    {
      pt_str = pt_str.concat(pt_list[j].x+t[0],",");
      pt_str = pt_str.concat(pt_list[j].y+t[1]," ");
    }
    //add last pt without trailing space
    pt_str = pt_str.concat(pt_list[j].x+t[0],",");
    pt_str = pt_str.concat(pt_list[j].y+t[1]);

    d3.select(elem).attr("points", pt_str);
  };

  //rotate everything in selected around midpoint of selected objects
  function rotateSelected(degrees)
  {
    if(selected.length == 0) return false;
    let origin = getSelectedCenter();
    let selectedPolys = getAllSelectedPolys();
    for(sel of selectedPolys) applyRotation(sel, degrees, origin);
    return true;
  };

  //translate selected elements a given [x,y]
  function translateSelected(translation)
  {
    let toTranslate;
    if(selected.length == 0) return false;
    for(sel of selected) 
    { 
      if(sel.nodeName == 'polygon') applyTranslation(sel, translation);
      else getAllChildPolys(sel).forEach(poly => applyTranslation(poly, translation));
    }
    return true;
  };

  //scale selected elements a given [x,y]
  function scaleSelected(scale, origin)
  {
    if(selected.length == 0) return false;
    let selectedPolys = getAllSelectedPolys();
    for(sel of selectedPolys) applyScale(sel,scale,origin);
    return true;
  };

  //darkens color of selected polygons
  function darkenSelected()
  {
    let selectedPolys = getAllSelectedPolys();
    for(sel of selectedPolys){
      d3.select(sel).attr('fill', d3.hsl(d3.select(sel).attr('fill')).darker(edit_sp/40));
    }
    return true;
  };

  //lightens color of selected polygons
  function brightenSelected()
  {
    let sel_elem;

    let selectedPolys = getAllSelectedPolys();
    for(sel of selectedPolys)
    {
      sel_elem = d3.select(sel);

      if(sel_elem.attr('fill') == 'black' || sel_elem.attr('fill') == d3.rgb('black'))
      {
        let new_color = d3.hsl('black');
        new_color.s = 0;
        new_color.l = 0.1;
        sel_elem.attr('fill', new_color);
      }
      sel_elem.attr('fill', d3.hsl(sel_elem.attr('fill')).brighter(edit_sp/40));
    }
    return true;
  };

  //adjusts saturation of selected elements by a given factor, set fullsat to true to fully saturate color of selected elements
  function adjustSaturation(factor, fullsat = false)
  {
    let new_color;
    let selectedPolys = getAllSelectedPolys();
    for(sel of selectedPolys)
    {
      sel_elem = d3.select(sel);
      new_color = d3.hsl(sel_elem.attr('fill'));
      if(fullsat == true) new_color.s = 1;
      else new_color.s *= factor;
      sel_elem.attr('fill', new_color);
    }
    return true;
  };

  //changes order of selected polygons in dom so that they are painted on top of unselected polys
  function raiseSelected()
  {
    let selectedPolys = getAllSelectedPolys();
    let toPush = [];
    for(let j = 0; j < shapes.length; j++)
    {
      for(let i = 0; i < selectedPolys.length; i++)
      {
        if(shapes[j].node().id == selectedPolys[i].id)
        {
          shapes[j].raise();
          toPush.push(shapes.splice(j,1)[0]);
          selectedPolys.splice(i,1);
          j--;
          break;
        }    
      }
    }
    shapes.push(...toPush);
    let groups = main_node.selectAll('g')._groups[0];
    for(let i = 0; i < groups.length; i++) for(sel of selected) if(sel == groups[i]){d3.select(groups[i]).raise(); break;}
    for(elem of point_elems) elem.raise();
  };

  //changes order of selected polygons in dom so that they are painted below unselected polys
  function lowerSelected()
  {
    let selectedPolys = getAllSelectedPolys();
    let toUnshift = [];
    for(let j = shapes.length-1; j > -1 ; j--)
    {
      for(sel of selectedPolys)
      {
        if(shapes[j].node().id == sel.id) 
        {
          shapes[j].lower();
          toUnshift.unshift(shapes.splice(j,1)[0]);
          break;
        }     
      } 
    }
    shapes.unshift(...toUnshift); 
    let groups = main_node.selectAll('g')._groups[0];
    for(let i = groups.length; i > -1; i--) for(sel of selected) if(sel == groups[i]){d3.select(groups[i]).lower(); break;}
  };

  //makes selected elements invisible and uninteractable until unhidden, if nothing selected unhides all hidden
  function hideSelected()
  {
    if(selected.length)
    {
      let selectedPolys = getAllSelectedPolys();
      for(sel of selectedPolys)
      {
        d3.select(sel).attr('stroke-width', 0);
        sel.style = 'visibility: hidden;';
        hidden.push(sel);
      }
      selected = [];
    }
    else if(hidden.length)
    {
      for(shape of hidden)
      {
        shape.style = 'visibility: visible;';
      }
      hidden = [];
    }
    else
    {
      //fallback in case shape fails to unhide
      if(last_operation == 73)
      {
        for(shape of shapes)
        {
          shape.attr('style', 'visibility: visible;');
        }
      }
    }
  };

  //makes selected elements more transparent, called when 'o' pressed
  function lowerOpacity()
  {
    let sel_elem;
    let selectedPolys = getAllSelectedPolys();
    for(sel of selectedPolys)
    {
      sel_elem = d3.select(sel);
      if(sel_elem.attr('opacity') == null) sel_elem.attr('opacity', 1-(edit_sp/40));
      else sel_elem.attr('opacity', parseFloat((sel_elem.attr('opacity')*(1-(edit_sp/40)).toFixed(4))));
    }
  };

  //makes selected elements less transparent, called when 'p' pressed
  function raiseOpacity()
  {
    let sel_elem;
    let selectedPolys = getAllSelectedPolys();
    for(sel of selectedPolys)
    {
      sel_elem = d3.select(sel);
      if(sel_elem.attr('opacity') == null || sel_elem.attr('opacity') > 1) sel_elem.attr('opacity',1);
      else sel_elem.attr('opacity', parseFloat((sel_elem.attr('opacity')*(1+edit_sp/40).toFixed(4))));
    }
  };

  //shift hue, called when ~ is pressed
  function hueShiftRight()
  {
    let new_color;
    let selectedPolys = getAllSelectedPolys();
    for(sel of selectedPolys)
    {
      sel_elem = d3.select(sel);
      new_color = d3.hsl(sel_elem.attr('fill'));
      if(sel_elem.attr('fill') == 'black') new_color = 'rgb(20,0,0)';
      else if(sel_elem.attr('fill') == 'white') new_color = 'rgb(230,255,255)';
      else if(isNaN(new_color.h))
      {
        new_color.h = edit_sp;
        new_color.s = 0.1;
      }
      else new_color.h += edit_sp;
      sel_elem.attr('fill', new_color);
    }
  };

  function animHueElem(sel)
  {
    animId = sel.attr('animId');
    if(sel.select('#hueAnim').empty())
    {
      if(animId == null) animId = 1;
      else animId = parseInt(animId) + 1;
      let colorStart = d3.rgb(sel.attr('fill')).toString();
      let colorMid = d3.hsl(colorStart);
      colorMid.h += 128;
      colorMid = d3.rgb(colorMid).toString();
      let colorEnd = d3.hsl(colorMid);
      colorEnd.h += 128;
      colorEnd = d3.rgb(colorEnd).toString();

      sel.append("animate")
         .attr("attributeName", "fill")
         .attr("values", colorStart+';'+colorMid+';'+colorEnd+';'+colorStart)
         .attr("dur", "4s")
         .attr("repeatCount", "indefinite")
         .attr("id", "hueAnim");
      
    }
    else
    {
      animId = parseInt(animId) - 1;
      sel.select('#hueAnim').remove();
    } 
    sel.attr('animId', animId);
  };

  //toggles continuous hue shifting animation on selected elements
  function animateHue()
  {
    let sel;
    let animId;
    let selectedPolys = getAllSelectedPolys();
    for(elem of selectedPolys)
    {
      sel = d3.select(elem);
      animHueElem(sel);
    }
  };

  function animFlashElem(sel)
  {
    animId = sel.attr('animId');
    if(sel.select('#flashAnim').empty())
    {
      if(animId == null) animId = 2;
      else animId = parseInt(animId) + 2;
      let colorStart = d3.rgb(d3.hsl(sel.attr('fill')).darker(0.8)).toString();
      let colorEnd = d3.hsl(colorStart).brighter(1.6);
      colorEnd = d3.rgb(colorEnd).toString();

      sel.append("animate")
         .attr("attributeName", "fill")
         .attr("values", colorStart+';'+colorEnd+';'+colorStart)
         .attr("dur", "2s")
         .attr("repeatCount", "indefinite")
         .attr("id", "flashAnim");
      
    }
    else 
    {
      sel.select('#flashAnim').remove();
      animId = parseInt(animId) - 2;
    }
    sel.attr('animId', animId);
  };

  //toggles continuous brightening and return back to original color of fills on selected elements
  function animateFlash()
  {
    let sel;
    let animId;
    let selectedPolys = getAllSelectedPolys();
    for(elem of selectedPolys)
    {
      sel = d3.select(elem);
      animFlashElem(sel);
    }
  };

  function animRotateElem(sel, origin)
  {
    animId = sel.attr('animId');
    if(sel.select('#rotateAnim').empty())
    {
      if(animId == null) animId = 4;
      else animId = parseInt(animId) + 4;
      
      sel.append("animateTransform")
         .attr("attributeName", "transform")
         .attr("attributeType", "XML")
         .attr("type", "rotate")
         .attr("from", "0 ".concat(origin[0], " ", origin[1]))
         .attr("to", "360 ".concat(origin[0], " ", origin[1]))
         .attr("dur", "12s")
         .attr("repeatCount", "indefinite")
         .attr("id", "rotateAnim");
      
    }
    else
    {
     sel.select('#rotateAnim').remove();
     animId = parseInt(animId) - 4;
    }
    sel.attr('animId', animId);
  };

  //animates rotating selected elements around center of selection
  function animateRotate()
  {
    let sel;
    let animId;
    let origin = getSelectedCenter();
    let selectedPolys = getAllSelectedPolys();
    for(elem of selectedPolys)
    {
      sel = d3.select(elem);
      animRotateElem(sel, origin);
    }
  };

  //copies color under cursor from bg image or polygon to all selected polygons
  function copyColor()
  {
    if(moused_over != null)
    {
      let sel_elem;
      let selectedPolys = getAllSelectedPolys();
      for(sel of selectedPolys)
      {
        sel_elem = d3.select(sel);
        sel_elem.attr('fill', d3.select(moused_over).attr('fill'));
      }
    }
    //if an image is currently loaded
    else if(img.width != 0)
    {
      let ctx = c._groups[0][0].getContext('2d');
      let data = ctx.getImageData(x_y[0], x_y[1], 1, 1).data;
      let new_color = "rgb(".concat(data[0],",",data[1],',',data[2],")");
      for(sel of selected) d3.select(sel).attr('fill', new_color);
    }
  };

  function joinSelected()
  {
    let join_group = main_node.append('g')
                              .attr('id', id_counter);
    let join_elem = join_group.node();
    id_counter++;
    //this is awful but its to avoid messing up layering
    let index;
    let dom_order = main_node.node().children;
    while(selected.length){
      for(let i = 0; i < dom_order.length; i++) 
      {
        index = selected.indexOf(dom_order[i]);
        //its weird but dom order actually is changing every time we call remove
        if(index != -1)
        { 
          join_elem.appendChild(d3.select(selected.splice(index,1)[0]).remove().node());
          i--;
        }
      }
    }
    selected.push(join_elem);
    return join_group;
  };

  function unjoinSelected()
  {
    let toPush = [];
    let before, children;
    for(let i = 0; i < selected.length; i++)
    {
      if(selected[i].nodeName == 'g')
      {
        before = selected[i].nextElementSibling;
        children = selected[i].children;
        while(children.length) toPush.push(main_node.node().insertBefore(children[0], before));
        d3.select(selected.splice(i,1)[0]).remove();
      }
    }
    selected.push(...toPush);
  };

  //called whenever any key is pressed
  function checkKey(e)
  {
    console.log(e.keyCode);
    //we dont want to add to undo history if we're hitting undo or redo
    if((e.keyCode != last_operation || e.keyCode == 13 || e.keyCode == 65) && e.keyCode != 85 && e.keyCode != 82) undo_history.push(undoElements());
    while(undo_history.length > max_undo_steps) undo_history.shift();

    last_operation = e.keyCode;

    //top row number keys, create polygon
    if(e.keyCode > 47 && e.keyCode < 58)
    {
      let color;

      switch(e.keyCode){
        case 49:
          color = colors[0];
          break;
        case 50:
          color = colors[1];
          break;
        case 51:
          color = colors[2];
          break;
        case 52:
          color = colors[3];
          break;
        case 53:
          color = colors[4];
          break;
        case 54:
          color = colors[5];
          break;
        case 55:
          color = colors[6];
          break;
        case 56:
          color = colors[7];
          break;
        case 57:
          color = colors[8];
          break;
        case 58:
          color = colors[9];
          break;
      }

      if(points.length > 2)
      {
        let pointsStr = "";
        pointsStr = pointsStr.concat(points[0][0],",",points[0][1]);

        for(let i = 1; i < points.length; i++)
        {
          pointsStr = pointsStr.concat(" ",points[i][0],",",points[i][1]);
        }
        
        shapes.push(main_node.append("polygon")
                             .attr("points", pointsStr)
                             .attr("fill", color)
                             .on("mouseover", function(){moused_over = this;})
                             .on("mouseleave", function(){if(moused_over == this) moused_over = null;})
                             .on("contextmenu", contextMenuCalled)
                             .attr('id', ''.concat(id_counter)));
        
                             
        id_counter++;

        point_elems.forEach(point => point.remove());
        points = [];
        point_elems = [];
      }

    }
    //arrow keys, translate selected
    if(e.keyCode >= 37 && e.keyCode <= 40)
    {
      e.preventDefault();
      let toTranslate;
      switch(e.keyCode)
      {
        case 37:
          translateSelected([-edit_sp,0]);
          break;
        case 38:
          translateSelected([0,-edit_sp]);
          break;
        case 39:
          translateSelected([edit_sp,0]);
          break;
        case 40:
          translateSelected([0,edit_sp]);
          break;
      }
    }
    //if d key is pressed, duplicate selected
    else if(e.keyCode == 68)
    {
      selected.forEach(elem => cloneElem(elem));
    }
    //if q pressed, delete selected
    else if (e.keyCode == 81)
    {
      selected.forEach(elem => removeElem(elem));
      selected = [];
    }
    //if < or > pressed, rotate selected
    else if(e.keyCode == 188 || e.keyCode == 190)
    {
      if(e.keyCode == 188) rotateSelected(-edit_sp/2);
      else rotateSelected(edit_sp/2);
    }
    //scale selected on n and m keys, mirror on x and y keys
    else if(e.keyCode == 78 || e.keyCode == 77 || e.keyCode == 88 || e.keyCode == 89)
    {
      let num_applications = edit_sp*2;
      let origin = getSelectedCenter();

      //78 (n) scales down
      if(e.keyCode == 78)      for(let i = 0; i < num_applications; i++) scaleSelected([0.9875,0.9875], origin);
      //77 (m) scales up
      else if(e.keyCode == 77) for(let i = 0; i < num_applications; i++) scaleSelected([1.0125,1.0125], origin);

      //mirror on x when x key is pressed
      else if(e.keyCode == 88) scaleSelected([-1,1], origin);
      //mirror on y when y key is pressed
      else if(e.keyCode == 89) scaleSelected([1,-1], origin);
    }
    //brighten color of selected if b is pressed
    else if(e.keyCode == 66) brightenSelected();
    //brighten color of selected if v is pressed
    else if(e.keyCode == 86) darkenSelected();
    //raise on ]
    else if(e.keyCode == 221) raiseSelected();
    //lower on [
    else if(e.keyCode == 219) lowerSelected();
    //select/deselect all on 's'
    else if(e.keyCode == 83) selectAll();
    //lower opacity on 'o'
    else if(e.keyCode == 79) lowerOpacity();
    //raise opacity on 'p'
    else if(e.keyCode == 80) raiseOpacity();
    //shift hue on ~
    else if(e.keyCode == 192) hueShiftRight();
    //toggle continuous hue animation on h
    else if(e.keyCode == 72) animateHue();
    //toggle continuous flash animation on f
    else if(e.keyCode == 70) animateFlash();
    //toggle continuous rotate animation on g
    else if(e.keyCode == 71) animateRotate();
    //lower saturation on numpad -
    else if(e.keyCode == 109) adjustSaturation(1-(edit_sp/40));
    //raise saturation on numpad +
    else if(e.keyCode == 107) adjustSaturation(1+(edit_sp/40));
    //fully saturate if numpad * is pressed
    else if(e.keyCode == 106) adjustSaturation(1,true);
    //fully desat on numpad .
    else if(e.keyCode == 46) adjustSaturation(0);
    //undo on 'u'
    else if(e.keyCode == 85) undo();
    //redo on 'r'
    else if(e.keyCode == 82) redo();
    //copy colors from other polygons or background image when c is pressed
    else if(e.keyCode == 67) copyColor();
    //hides selected elements if there are any, otherwise unhides all hidden
    else if(e.keyCode == 73) hideSelected();
    //join selected elements on 'j'
    else if(e.keyCode == 74) joinSelected();
    //unjoin selected elements on 'k'
    else if(e.keyCode == 75) unjoinSelected();
  };
  </script>
  <script>
    if('' + '<?=$_SESSION["user"] ?>'){
      document.getElementById('profile').innerHTML = '<form action="php/logout.php" method="post"> Logged in as ' + '<?=$_SESSION["user"]?>' + ' <input type="submit" value="Logout"> </form>';
      console.log('<?=$_SESSION["user"] ?>');
    }
  </script>

</html>