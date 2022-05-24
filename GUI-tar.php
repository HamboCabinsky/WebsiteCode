<!DOCTYPE html>
<html lang="">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GUI-tar</title>
  <style>
    main {
        max-width: 700px;
        margin: auto;
        max-height: 300px;
        transform: translate(0%, 5%);
        user-select: none;
      }
  </style>
  <link rel="stylesheet" media="screen" href="defstylsh.css">
  <link rel="shortcut icon" type="image/jpg" href="painty.jpg">
  <script src="https://cdn.jsdelivr.net/npm/p5@1.2.0/lib/p5.min.js"></script>
</head>
<body>
  <div class="nav">
      <a href="index.php">Home</a>
      <a href="cards.php">Guessing Game</a>
      <a class="active" href="GUI-tar.php">GUI-tar</a>
      <a href="polyform.php">PolyForm</a>
      <a href="featured.php">Featured Arts</a>
  </div>
  <br>
  <section class="title-container">
        <h1 id="title">GUI-tar</h1>
        <p class="sub-title">Guitar fretboard plotter for any key, any custom tuning!.</p>
  </section>
  <main>
    <div id='p5cont'></div>
  </main>
</body>

<script>

  let NoteList = (function(){
    let notelist = function(rootTone = null, notes = ["C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"]){
      this.rootTone = rootTone;
      this.notes = notes;
      if(rootTone) while(notes[0] != rootTone) notes.push(notes.shift());
      else this.rootTone = this.notes[0];
    };

    notelist.prototype.shiftRootRight = function() {
      this.notes.push(this.notes.shift());
      this.rootTone = this.notes[0];
    };

    notelist.prototype.shiftRootLeft = function() {
      this.notes.unshift(this.notes.pop());
      this.rootTone = this.notes[0];
    };

    notelist.prototype.getMinorPentatonic = function() {
      return [this.notes[0],this.notes[3],this.notes[5],this.notes[7],this.notes[10]];
    };

    notelist.prototype.getMajorPentatonic = function() {
      return [this.notes[0],this.notes[2],this.notes[4],this.notes[7],this.notes[9]];
    };

    notelist.prototype.getMinor = function() {
      return [this.notes[0],this.notes[2],this.notes[3],this.notes[5],this.notes[7],this.notes[8],this.notes[10]];
    };

    notelist.prototype.getMajor = function() {
      return [this.notes[0],this.notes[2],this.notes[4],this.notes[5],this.notes[7],this.notes[9],this.notes[11]];
    };

    notelist.prototype.getBlues = function() {
      return [this.notes[0],this.notes[3],this.notes[5],this.notes[6],this.notes[7],this.notes[10]];
    };

    function containsNote(scale, note){
      for(let i = 0; i < scale.length; i++)
      {
        if(note == scale[i]) return true;
      }
      return false;
    };

    notelist.prototype.paintNotes = function(scale, yPos, key) {
      let xPos = 116.5;
      let currentNote;
      let fretwidth = 33;
      strokeWeight(0);
      for(let i = 0; i < 18; i++)
      {
        currentNote = this.notes[i%12];
        if(containsNote(scale,currentNote))
        {
          fill("black");
          if(currentNote == key) fill("red");
          ellipse(xPos + i*fretwidth, yPos, 28, 28);
          fill("white");
          text(currentNote, xPos + i*fretwidth - 4, yPos + 4);
        }
      }
    };

    return notelist;
  })();

  let StrButton = (function(){
    let strbutton = function(pos=null, string=null, lr="right", size=16){
      this.pos = pos;
      //string defining if button is oriented "left" or "right"
      this.lr = lr;
      this.string = string;
      this.dimensions = [this.pos[0],this.pos[1],this.pos[0]+size,this.pos[1]+size];
      this.size = size;
    };

    strbutton.prototype.clicked = function() {
      if(this.lr == "right")  this.string.shiftRootRight();
      else this.string.shiftRootLeft();
    };

    strbutton.prototype.paintButton = function() {

      if(this.lr == "right")
      {
        fill('black');
        beginShape();
        vertex(this.dimensions[0],this.dimensions[1]);
        vertex(this.dimensions[0],this.dimensions[3]);
        vertex(this.dimensions[2],this.dimensions[1]+this.size/2);
        endShape(CLOSE);
      }
      else
      {
        fill('black');
        beginShape();
        vertex(this.dimensions[2],this.dimensions[3]);
        vertex(this.dimensions[2],this.dimensions[1]);
        vertex(this.dimensions[0],this.dimensions[1]+this.size/2);
        endShape(CLOSE);
      }
      
    };

    return strbutton;
  })();

  let keyList;
  let scale;

  let str6Notes;
  let str5Notes;
  let str4Notes;
  let str3Notes;
  let str2Notes;
  let str1Notes;
  let strings;

  let keyLArrDims;
  let keyRArrDims;

  function setup() {
    canv = createCanvas(700,300);

    keyList = new NoteList("A");
    scale = keyList.getMinorPentatonic();
    keyLArrDims = [12,258,28,274];
    keyRArrDims = [74,258,90,274];

    str6Notes = new NoteList("E");
    str5Notes = new NoteList("B");
    str4Notes = new NoteList("G");
    str3Notes = new NoteList("D");
    str2Notes = new NoteList("A");
    str1Notes = new NoteList("E");

    strings = [str6Notes,str5Notes,str4Notes,str3Notes,str2Notes,str1Notes];
    strButtons = [];
    for(let i = 0; i < strings.length; i++)
    {
      strButtons.push(new StrButton([62,58+i*30],strings[i]));
      strButtons.push(new StrButton([22,58+i*30],strings[i],"left"));
    }
  
    sel = createSelect();
    sel.option('minor pentatonic');
    sel.option('major pentatonic');
    sel.option('minor');
    sel.option('major');
    sel.option('blues');
    sel.changed(updateScale);
    
    canv.parent('p5cont');
    sel.parent('p5cont');
    
  };

  function draw() {
    background('white');
    fill('#fac682');
    let fretwidth = 33;
    stroke('gray');
    strokeWeight(1);
    for(let i = 0; i < 17; i++)
    {
      rect(133+fretwidth*i,50,fretwidth,187);
    }

    fill('black');
    for(let i = 0; i <= 17; i++)
    {
      if(i < 10) text(i,112+i*fretwidth,40);
      else text(i,108+i*fretwidth,40);
    }
    
    strokeWeight(0);
    fill('silver');

    //paint dots for frets 3, 5, 7, 9
    for(let i = 0; i < 4; i++)
    {
      ellipse(215.5+i*fretwidth*2,143.5,28,28);
    }

    //paint 2 dots for fret 12
    ellipse(512.5,111,28,28);
    ellipse(512.5,171,28,28);

    //paint dot for 15th fret
    ellipse(512.5+fretwidth*3,143.5,28,28);

    stroke('black');
    for(let i = 0; i < 6; i++)
    {
      strokeWeight(i+1);
      line(100,66+i*30,694,66+i*30);
      strokeWeight(0);
      fill('black');
      text(strings[i].notes[0],46,66+i*30+4);
    }

    strokeWeight(.5);
    fill('black');


    //key left arrow
    beginShape();
    vertex(28,274);
    vertex(28,258);
    vertex(12,266);
    endShape(CLOSE);

    text("Key: " + keyList.rootTone, 34, 270);

    //key right arrow
    beginShape();
    vertex(74,274);
    vertex(74,258);
    vertex(90,266);
    endShape(CLOSE);

    str6Notes.paintNotes(scale, 66, keyList.rootTone);
    str5Notes.paintNotes(scale, 96, keyList.rootTone);
    str4Notes.paintNotes(scale, 126, keyList.rootTone);
    str3Notes.paintNotes(scale, 156, keyList.rootTone);
    str2Notes.paintNotes(scale, 186, keyList.rootTone);
    str1Notes.paintNotes(scale, 216, keyList.rootTone);

    strButtons.forEach(button => button.paintButton());

  };

  function contains(dimensions,x,y){
    if((x > dimensions[0]) && (x < dimensions[2]) && (y > dimensions[1]) && (y < dimensions[3]))  return true;
    else return false; 
  };

  function updateScale()
  {
    if(sel.value() == "minor pentatonic") scale = keyList.getMinorPentatonic();
    else if(sel.value() == "major pentatonic") scale = keyList.getMajorPentatonic();
    else if(sel.value() == "minor") scale = keyList.getMinor();
    else if(sel.value() == "major") scale = keyList.getMajor();
    else if(sel.value() == "blues") scale = keyList.getBlues();
  }

  function mouseClicked(){
    if(contains(keyLArrDims, mouseX, mouseY))
    {
      keyList.shiftRootLeft();
      updateScale();
    }
    else if(contains(keyRArrDims, mouseX, mouseY))
    {
      keyList.shiftRootRight();
      updateScale();
    }

    strButtons.forEach(button => contains(button.dimensions,mouseX,mouseY) && button.clicked());

  };

</script>
<br>
<br>
<br>
<br>
<footer>
<div class="center">
  Copyright 2022 Christian Bonin
</div>
</footer>

</html>