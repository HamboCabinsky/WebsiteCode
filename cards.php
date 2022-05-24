<!DOCTYPE html>
<html lang="">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guessing Game</title>
  
  <style>
    main {
      /*padding: 10; 
      margin: 10;*/
      max-width: 400px;
      margin: auto;
      max-height: 400px;
      transform: translate(0%, 5%);
    }
  </style>
  <link rel="stylesheet" media="screen" href="defstylsh.css">
  <link rel="shortcut icon" type="image/jpg" href="painty.jpg">
  <script src="https://cdn.jsdelivr.net/npm/p5@1.2.0/lib/p5.min.js"></script>
  
</head>
<body>
  <div class="nav">
      <a href="index.php">Home</a>
      <a class="active" href="cards.php">Guessing Game</a>
      <a href="GUI-tar.php">GUI-tar</a>
      <a href="polyform.php">PolyForm</a>
      <a href="featured.php">Featured Arts</a>
  </div>
  <br>
  <section class="title-container">
        <h1 id="title">Card Guessing Game</h1>
        <p class="sub-title">Click on cards in your hand to choose what you will play.</p>
        <p class="sub-title">Guess the numeric card value the AI will play in the input box and press play turn. </p>
        <p class="sub-title">Numeric cards are their numeric value, face cards have a value of 10. </p>
        <p class="sub-title">If you play an Ace, you will be prompted to choose if you want to play it as 1 or 11. </p>
  </section>
  <main>
    <div id='p5cont'></div>
  </main>
</body>

<script>

    //Takes a given set (as an array) and returns every possible subset of it
    //  as an array of arrays
    function powerset(uniSet){
      //for each element, the element is either there or not, therefore 
      //  2 ^ (# elements) possible sets
      let num_sets = 2 ** uniSet.length;
      let set_list = [];
      
      //the binary representations of the numbers ranging (0,num_sets) give us 
      //  all possible ways a bit array of size setlength can be activated
      //    (for len 3 for ex, 000, 001, 010, 011, 100, 101, 110, 111)
      for(let i = 0; i < num_sets; i++)
      {
        //for each binary number, make a new set and populate it with every
        //  element in the same position in the universal set as an activated
        //    bit in the current number
        let new_set = [];
        for(let j = 0; j < uniSet.length; j++)
        {
          //to check if the bit is set, take one and bitshift it left then
          //  check if it returns a value when anded with current number
          if((1 << j)&i)
          {
            //if it does, add it to the current set
            new_set.push(uniSet[j]);
          }
        }
        set_list.push(new_set);
      }
      return set_list;
    };

    //IIFE Class Definition to avoid global namespace pollution
    let CardSet = (function(){

      //    -- Class Constructor for CardSet Class --
      //default numerical values are assigned to each card, either a single 
      //  int or an array of 2 ints to signify that the card can have either value
      //    depending on the situation (as is common with the ace in many games)
      let cardset = function(card_set=[]){
        this.cards = card_set;
      };
      //Blackjack values by default
      let card_values = {
          "2":2,
          "3":3,
          "4":4,
          "5":5,
          "6":6,
          "7":7,
          "8":8,
          "9":9,
          "10":10,
          "J":10,
          "Q":10,
          "K":10,
          "A":[1,11]
      };

      cardset.prototype.get_card_values = function(){
        return card_values;
      };

      cardset.prototype.set_card_value = function(key, value){
        card_values[key] = value;
      };

      cardset.prototype.add = function(card){
        this.cards.push(card);
      };

      cardset.prototype.remove = function(card){
        let rem_index = this.cards.indexOf(card);
        if(rem_index == -1) return null;
        return this.cards.splice(rem_index,1)[0];
      };



      //Helper function for all_possible_plays, simply converts
      //  str[] of card names to an int/[] array of corresponding score values
      function conv2Scores(subSet)
      {
        let score_set = [];

        for(let i = 0; i < subSet.length; i++){
          if(!card_values[subSet[i]]){
            console.log("No value defined for card: " + card);
            return null;
          }
          score_set.push(card_values[subSet[i]]);
          
        }
        return score_set;
      };

      //Designed primarily to aid the all_possible_plays function below
      //  takes a list representing card values for a given play, cards
      //    that only have 1 possible value are represented by ints, while
      //      cards w/ 2 possible values are len 2 int[]s. Returns all possible
      //        ways the play can be scored. (ex: [1,3,[1,11]] => [5,15]; 
      //          [1,[1,11],[2,3]] => [4,14,5,15]) (# of scores returned is 2^(#int[]s))
      function evaluate_play(subSet)
      {
        //will abort if null array passed (occurs when conv2Scores fails)
        if(subSet == null)  return;

        //each 2 element list representing a card that can be one
        //  of 2 possible score values (as is common with the ace)
        let binary_cards = [];

        //all possible sums that could result from this play
        let sums = [];

        //will hold our sum of the ints in the subset w/o any binary cards
        let initial_sum = 0;

        //iterate through subset and add each integer to initial_sum
        //  any element that is not int should be 2 element int[], each
        //    of those is pushed to binary_cards for further processing
        subSet.forEach(element => Number.isInteger(element) ? initial_sum += element : binary_cards.push(element));

        //same concept as powerset, for each bin card we can choose 1 of 2
        //  values to add to the sum, so there are 2 ^ (# bin cards) 
        //    possible scores that can be produced from each hand
        let possible_sums = 2 ** binary_cards.length;

        for(let i = 0; i < possible_sums; i++){
          //will start w/ sum of integer elements
          let new_sum = initial_sum;

          for(let j = 0; j < binary_cards.length; j++){
            //if the bit is set in i (curr number), add the second
            //  possible card value, otherwise add the first
            if((1 << j)&i){
              new_sum += binary_cards[j][1];
            }else{
              new_sum += binary_cards[j][0];
            }
          }

          sums.push(new_sum);
        }

        return sums;
      };


      //Takes a list of strings representing a set of cards and computes every
      //  possible score value one could play with the given set of cards
      cardset.prototype.all_possible_plays = function()
      {
        let card_set = this.cards;
        //will hold every possible score producible w/ given cards
        let play_set = [];
        //every possible combination of cards that can be played
        let pSet = powerset(card_set);

        //call evaluate_play on each subset to get all possible card
        //  scorings for each hand, then push every element that is
        //    returned onto a list of all possible scorings (... expands arr, to
        //      allow the pushing of multiple items. Similar to Python list extension)
        pSet.forEach(subSet => play_set.push(...evaluate_play(conv2Scores(subSet))));

        return play_set;
      };

      //given a card_set, returns a frequency distribution of all possible scores
      //  producable with the given set of cards
      cardset.prototype.play_freq_distribution = function(sortby="value")
      {
        let plays = this.all_possible_plays();
        let play_freq_dict = {};
        for(let i = 0; i < plays.length; i++){
          if(play_freq_dict[plays[i]]){
            play_freq_dict[plays[i]] += 1;
          }else{
            play_freq_dict[plays[i]] = 1;
          }
        }
        //turns the dictionary into an array of arrays for sorting
        let scores = Object.keys(play_freq_dict).map(key => [key,play_freq_dict[key]]);

        //custom defined comparator to sort by 2nd element of each []
        if(sortby == "value") 
          scores.sort((first, second) => second[1]-first[1]);
        else
          scores.sort((first, second) => second[0]-first[0]);
        return scores;
      };

      cardset.prototype.get_random_subset = function()
      {
       let rand_bitset = Math.floor((2 ** this.cards.length) * Math.random());
       let subset = [];
       for(let i = 0; i < this.cards.length; i++){
          //checking each bit and adding corresponding elements from the
          //  cards [] to subset if the bit is set
          if((1 << i)&rand_bitset) subset.push(this.cards[i]);
        }
        return subset;
      };

      //returns a 2 element array containing A) an [] of a random number of 
      //  random cards from the set and B) a random score that could be made
      //    from subset of cards, b/c cards can have up to 2 values
      cardset.prototype.get_random_play = function()
      {
        let played_cards = this.get_random_subset();
        let possible_sums = evaluate_play(conv2Scores(played_cards));
        let score_index = Math.floor(Math.random()*possible_sums.length);
        //console.log(played_cards,possible_sums,score_index);
        let score_played = possible_sums[score_index];
        return [played_cards, score_played];
      };

      return cardset;
    })();
    //END CARDSET CLASS

    let Deck = (function(){

      let full_deck = ["A","A","A","A","K","K","K","K","Q","Q","Q","Q","J","J","J","J","10","10","10","10","9","9","9","9","8","8","8","8","7","7","7","7","6","6","6","6","5","5","5","5","4","4","4","4","3","3","3","3","2","2","2","2"];

      let deck = function(cards=full_deck, pos=null){
        this.cards = cards;
        this.pos = pos;
      };

      deck.prototype = Object.create(CardSet.prototype);
      deck.prototype.constructor = deck;

      //shuffles the card deck
      deck.prototype.shuffle = function(){
        for(let i = this.cards.length-1; i > 0; i--){
          let j = Math.floor(Math.random() * i);
          let temp = this.cards[j];
          this.cards[j] = this.cards[i];
          this.cards[i] = temp;
        }
      };

      //splits this deck into an array of 2 decks and returns it
      deck.prototype.split = function(){
        let mid_ind = Math.floor(this.cards.length/2);
        let d1 = new Deck(this.cards.slice(0,mid_ind));
        let d2 = new Deck(this.cards.slice(mid_ind));
        return [d1,d2];
      };

      deck.prototype.draw = function(){
        return this.cards.pop();
      };

      deck.prototype.show = function(){
        if(this.pos == null)  return;
        fill(color(0,0,100));
        rect(this.pos[0],this.pos[1],50,100);
        fill(color(255,255,255));
        text(this.cards.length,this.pos[0]+10,this.pos[1]+20);
        text("Cards",this.pos[0]+10,this.pos[1]+40);
        text("Left",this.pos[0]+10,this.pos[1]+60);

      };

      return deck;
    })();
    //END DECK CLASS

    let Hand = (function(){

      let hand = function(pos = null){
        CardSet.call(this);
        this.pos = pos;
        //indices of selected cards
        this.selected = [];
        //x & y where each card begins and ends
        this.card_dimensions = [];
        
      };

      hand.prototype = Object.create(CardSet.prototype);
      hand.prototype.constructor = hand;

      hand.prototype.show = function(){
        if(this.pos == null)  return;
        let curr_x = this.pos[0];
        for(let i = 0; i < this.cards.length; i++)
        {
          noStroke();
          if(this.selected[i]){
            stroke(color(255,255,0));
            strokeWeight(2);
          }else{
            stroke(color(0,0,0));
            strokeWeight(1);
          }
          fill(color(245,245,255));
          rect(curr_x, this.pos[1], 50, 100);
          
          fill(color(0,0,0));
          text(this.cards[i],curr_x+5,this.pos[1]+10);
          curr_x += 50;
          stroke(color(0,0,0));
          strokeWeight(1);
        }

      };

      hand.prototype.update_dims = function(){
        let curr_x;
        if(this.pos != null) {
          curr_x = this.pos[0];
          for(let i = 0; i < this.cards.length; i++){
            this.card_dimensions.push([curr_x, this.pos[1], curr_x+50, this.pos[1]+100]);
            curr_x += 50;
          }
        }
      };

      hand.prototype.get_selected = function(){
        let sel_elems = [];
        for(let i = 0; i < this.selected.length; i++)
        {
          if(this.selected[i] && this.cards[i]) sel_elems.push(this.cards[i]);
        }
        return sel_elems;
      };

      return hand;
    })();

    let full_deck;
    let player_decks;
    let p1_hand;
    let p2_hand;
    let max_hand;
    let input;
    let button;
    let status;
    let canv;

  	function setup() {
  		canv = createCanvas(400, 400);
      full_deck = new Deck();
      full_deck.shuffle();
      player_decks = full_deck.split();
      
      player_decks[0].pos = [width-90,height-150];
      player_decks[1].pos = [40,50];

      p1_hand = new Hand([150, height-150]);
      p2_hand = new Hand([100,50]);

      max_hand = 3;
      for(let i = 0; i < max_hand; i++){
        p1_hand.add(player_decks[0].draw());
        p2_hand.add(player_decks[1].draw());
      }
      p1_hand.update_dims();

      p1_hand.selected = [0,0,0];

      status = "";
      //max-width: 400px;
      //margin: auto;

      label = createElement('p', 'Enter Guess:');
      input = createInput();
      button = createButton('Play Turn');
      canv.parent('p5cont');
      label.parent('p5cont');
      input.parent('p5cont');
      button.parent('p5cont');
      
      button.mousePressed(play_turn);
      
    };

    function draw() {
    	background(color(0,50,0));
      player_decks.forEach(deck => deck.show());
      p1_hand.show();
      p2_hand.show();
      fill(color(255,0,0));
      text(status,width/5,height/2.2);
    };

    function play_turn(){
      //get number input
      let guess = parseInt(input.value(),10);
      input.value('');

      if(Number.isNaN(guess))  return;
      //calculate AI's turn
      let AI_turn = p2_hand.get_random_play();
      //reroll once if score is 0
      if(!AI_turn[1]) AI_turn = p2_hand.get_random_play();

      status = "AI played a: " + AI_turn[1] + " You Guessed: " + guess;

      //calculate AI's Guess
      let roll = Math.random();
      let AI_guess;
      let freq;
      //pick a high scoring possible score
      if(roll < .4){
        freq = p1_hand.play_freq_distribution("key");
        ind = Math.floor(Math.random()*(p1_hand.cards.length *0.2));
        AI_guess = freq[ind][0];
      //pick a common possible score
      }else if(roll < .8){
        freq = p1_hand.play_freq_distribution();
        ind = Math.floor(Math.random()*(p1_hand.cards.length *0.2));
        AI_guess = freq[ind][0];
      //pick a random possible score
      }else{
        freq = p1_hand.play_freq_distribution();
        AI_guess = freq[Math.floor(Math.random()*freq.length)][0];
      }
      
      //Get selected cards, ask if play ace as 1 or 11
      let sel_elems = p1_hand.get_selected();
      let played_score = 0;
      let val_dict = p1_hand.get_card_values();

      //Get players played score
      for(let i = 0; i < sel_elems.length; i++){
        if(sel_elems[i] == "A"){
          let val = prompt("You selected an ace, enter 1 or 11 to choose what value to play it as:");
          if(val == "11") played_score += 11;
          else played_score += 1;
        }else{
          if(val_dict[sel_elems[i]]) played_score += val_dict[sel_elems[i]];
        }
      }

      status += "\n AI Guessed a: " + AI_guess + " You Played: " + played_score;

      //console.log(AI_turn[0]);
      //Only get to discard if opponent's guess is wrong
      if(guess != AI_turn[1]){ 
        AI_turn[0].forEach(card => player_decks[0].add(p2_hand.remove(card)));
        player_decks[0].shuffle();
      }

      //compare AI's guess against played score
      if(AI_guess != played_score){
        sel_elems.forEach(card => player_decks[1].add(p1_hand.remove(card)));
        player_decks[1].shuffle();
      }

      while((p1_hand.cards.length < max_hand) && player_decks[0].cards.length) p1_hand.add(player_decks[0].draw());

      while((p2_hand.cards.length < max_hand) && player_decks[1].cards.length) p2_hand.add(player_decks[1].draw());

      if(!p1_hand.cards.length && !player_decks[0].length)
      {
        status += "\n YOU WIN!!";
      }
      if(!p2_hand.cards.length && !player_decks[1].length)
      {
        status += "\n YOU LOSE!!";
      }


    };

    function contains(dimensions,x,y){
      if((x > dimensions[0]) && (x < dimensions[2]) && (y > dimensions[1]) && (y < dimensions[3]))  return true;
      else return false; 
    };

    function mouseClicked()
    {
      for(let i = 0; i < p1_hand.card_dimensions.length; i++)
      {
        if (contains(p1_hand.card_dimensions[i], mouseX, mouseY)){
          if(p1_hand.selected[i]) p1_hand.selected[i] = 0; 
          else p1_hand.selected[i] = 1;
        }
      }
    };

</script>
<br>
<br>
<br>
<br>
<br>
<br>
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