let ExpressionTree = (function(){
  let tree = function(solved = false)
  {
    this.value = null;
    this.lhs = null;
    this.rhs = null;
    this.string = null;
    this.solved = solved;
  };

  tree.prototype.construct = function(PF)
  {
    let treeStack = [];
    let T1, T2, val = '';

    for(let i = 0; i < PF.length; i++)
    {
      if(PF[i] == ' ') continue;
      else if(isOperand(PF[i]))
      {
        while(isOperand(PF[i]))
        {
          val += PF[i];
          i++;
        }
        let t = new ExpressionTree();
        t.value = val;
        treeStack.push(t);
        val = '';
      }
      else if(isOperator(PF[i]))
      {
        val += PF[i];
        T1 = treeStack.pop();
        T2 = treeStack.pop();

        let t = new ExpressionTree();
        t.value = val;
        t.lhs = T2;
        t.rhs = T1;
        treeStack.push(t);
        val = "";
       }
    }
    let temp = treeStack.pop();
    this.value = temp.value;
    this.lhs = temp.lhs;
    this.rhs = temp.rhs;

  };

  tree.prototype.clone = function(){
    let temp = new ExpressionTree();
    temp.value = this.value;
    if(this.lhs != null) temp.lhs = this.lhs.clone();
    if(this.rhs != null) temp.rhs = this.rhs.clone();
    return temp;
  };

  tree.prototype.deriv = function()
  {
    let T1, T2, T3, T4, T5;
    if (isAlpha(this.value[0])) value = "1";
    else if(isDigit(this.value[0])) value = "0";
    else
    {
      switch(this.value[0])
      {
        case '+':
        case '-':
          this.lhs.deriv();
          this.rhs.deriv();
          break;
        case '*':
          //if both products are numeric, result constant therefore deriv is 0
          if(isDigit(this.lhs.value[0]) && isDigit(this.rhs.value[0]))
          {
            this.lhs = null;
            this.rhs = null;
            this.value = 0;
          }
          //we dont need to do product rule bc constant times a function
          else if(!this.lhs.lhs)
          {
            T1 = this.lhs.clone();
            T2 = this.rhs.clone();
            T2.deriv();
            this.value = "*";
            this.lhs = T1;
            this.rhs = T2;
          }
          else if(!this.rhs.rhs)
          {
            T1 = this.lhs.clone();
            T2 = this.rhs.clone();
            T1.deriv();
            this.value = "*";
            this.lhs = T1;
            this.rhs = T2;

          }
          //full product rule necessary
          else
          {
            T1 = this.lhs.clone();
            T2 = this.rhs.clone();
            T3 = this.lhs.clone();
            T4 = this.rhs.clone();

            this.value = "+";
            this.lhs.value = "*";
            this.rhs.value = "*";
            this.lhs.lhs = T1;
            T1.deriv();
            this.lhs.rhs = T2;
            this.rhs.lhs = T3;
            this.rhs.rhs = T4;
            T4.deriv();
          }
          
          break;
        case '/':
          T1 = this.lhs.clone();
          T2 = this.rhs.clone();
          T3 = this.lhs.clone();
          T4 = this.rhs.clone();
          T5 = this.rhs.clone();

          this.lhs.value = "-";
          this.lhs.lhs = new ExpressionTree();
          this.lhs.lhs.value = '*';

          this.lhs.lhs.lhs = T1;
          T1.deriv();
          this.lhs.lhs.rhs = T2;

          this.lhs.rhs.lhs = T3;
          T3.deriv();
          this.lhs.rhs.rhs = T4;

          this.rhs.value = "^";
          this.rhs.lhs = T5;
          this.rhs.rhs.value = "2";
          break;
        case '^':
          let base = this.lhs.clone();
          let power = this.rhs.clone();
          this.value = "*";
          if(isDigit(power.value[0]) && isDigit(base.value[0]))
          {
            this.value = "0";
          }
          else if(isDigit(power.value[0]))
          {
            this.rhs = new ExpressionTree();
            this.rhs.value = '^';

            let double = parseFloat(power.value);

            this.rhs.rhs = new ExpressionTree();
            this.rhs.rhs.value = '' + (double - 1);

            this.rhs.lhs = base.clone();

            if(!isOperand(base.value[0]))
            {
              let toDeriv = base.clone();
              toDeriv.deriv();
              this.lhs = new ExpressionTree();
              this.lhs.value = "*";
              this.lhs.lhs = toDeriv;
              this.lhs.rhs = power.clone();
            }
            else this.lhs = power.clone();
          }
          else
          {
            this.lhs = new ExpressionTree();
            this.lhs.value = '^';

            this.lhs.lhs = base.clone();
            this.lhs.rhs = power.clone();

            if(power.lhs != null && power.rhs != null)
            {
              this.rhs = new ExpressionTree();
              this.rhs.value = "*";
              this.rhs.lhs = power.clone();
              this.rhs.lhs.deriv();
              this.rhs.rhs = new ExpressionTree();
              this.rhs.rhs.value = '~';
              this.rhs.rhs.lhs = new ExpressionTree();
              this.rhs.rhs.lhs.value = "ln";
              this.rhs.rhs.rhs = base.clone();
            }
            else
            {
              this.rhs = new ExpressionTree();
              this.rhs.value = '~';
              this.rhs.lhs = new ExpressionTree();
              this.rhs.lhs.value = "ln";

              this.rhs.rhs = new ExpressionTree();
              this.rhs.rhs = base.clone();
            }
          }
      }
    }
  };

  function infix(tree, strRef)
  {
    if(tree.lhs != null) strRef[0] += '(';
    if(tree.lhs != null) infix(tree.lhs, strRef);
    strRef[0] += tree.value;
    if(tree.rhs != null) infix(tree.rhs, strRef);
    if(tree.rhs != null) strRef[0] += ')';
    return strRef[0];
  };

  tree.prototype.toString = function()
  { 
    this.string = '';
    let reference = [this.string];
    this.string += infix(this.lhs, reference);
    this.string += this.value;
    this.string += infix(this.rhs, reference);
    return this.string;
  };


  return tree;
})();

//used by solveStep
function getOpNode(tree, depth, prev = null)
{
  let left, right;
  if(tree.lhs) left = getOpNode(tree.lhs,depth+1,tree);
  if(tree.rhs) right = getOpNode(tree.rhs,depth+1,tree);

  if(left == null && right == null) return[tree, depth, prev];
  else
    if(left[1] > right[1]) return left;
    else return right;
};

function removeOpNode(tree)
{
  let opNode = getOpNode(tree, 1)[2];
  opNode.lhs = null;
  opNode.rhs = null;
  return opNode;
};

//pass in solveFor as string
function solveStep(tree, solveFor)
{
  let opNode = getOpNode(tree, 1)[2];
  opNode.solved = true;
  if(opNode.lhs.value != 'ln' && isAlpha(opNode.lhs.value[0])) opNode.lhs.value = solveFor;
  if(isAlpha(opNode.rhs.value[0])) opNode.rhs.value = solveFor;

  switch(opNode.value)
  {
    case '+':
      opNode.value = "" + (parseFloat(opNode.lhs.value) + parseFloat(opNode.rhs.value));
      opNode.lhs = null;
      opNode.rhs = null;
      break;
    case '-':
      opNode.value = "" + (parseFloat(opNode.lhs.value) - parseFloat(opNode.rhs.value));
      opNode.lhs = null;
      opNode.rhs = null;
      break;
    case '*':
      opNode.value = "" + (parseFloat(opNode.lhs.value) * parseFloat(opNode.rhs.value));
      opNode.lhs = null;
      opNode.rhs = null;
      break;
    case '/':
      opNode.value = "" + (parseFloat(opNode.lhs.value) / parseFloat(opNode.rhs.value));
      opNode.lhs = null;
      opNode.rhs = null;
      break;
    case '^':
      opNode.value = "" + (parseFloat(opNode.lhs.value) ** parseFloat(opNode.rhs.value));
      opNode.lhs = null;
      opNode.rhs = null;
      break;
    case '~':
      console.log(opNode);
      switch(opNode.lhs.value)
      {
        case 'ln':
          opNode.value = "" + Math.log(parseFloat(opNode.rhs.value));
          opNode.lhs = null;
          opNode.rhs = null;
          break;
      }
  }
};

//returns a new string with toRemove removed
function removeFromString(string, toRemove)
{
  let beginIndex = string.indexOf(toRemove);
  if(beginIndex == -1) return string;
  let endIndex = beginIndex + toRemove.length;
  return string.slice(0,beginIndex) + string.slice(endIndex,string.length);
};

//returns a new string with toInsert added at given index insertIndex
function stringInsert(string, toInsert, insertIndex)
{
  let strBegin = string.slice(0,insertIndex);
  let strEnd = string.slice(insertIndex,string.length);
  return strBegin.concat(toInsert,strEnd);
};

function cleanForParsing(exp)
{
  exp = exp.split(/\s+/).join('');
  for(let i = 1; i < exp.length; i++) if(isDigit(exp[i-1]) && isAlpha(exp[i])) exp = stringInsert(exp, '*', i);
  return exp;
};

function infixToPostfix(infix)
{
  let pf_str = '';
  let opStack = [];
  let currOp;

  infix = cleanForParsing(infix);
  for(let i = 0; i < infix.length; i++)
  {
    if(infix[i] == '(')
    {
      opStack.push(infix[i]);
    }
    else if(infix[i] == ')')
    {
      currOp = opStack.pop();  
      while(currOp != '(')
      {
        pf_str += currOp + ' ';
        currOp = opStack.pop();
      }
    }
    else if(isOperator(infix[i]))
    {
      if(opStack.length == 0) opStack.push(infix[i]);
      else
      {
        if(getPrecedence(infix[i]) > getPrecedence(opStack[opStack.length-1]))
          opStack.push(infix[i]);
        else
        {
          while(opStack.length != 0 && getPrecedence(opStack[opStack.length-1]) > getPrecedence(infix[i]))
            pf_str += opStack.pop() + ' ';

          opStack.push(infix[i]);
        }
      }
    }
    else
    {
      if(isDigit(infix[i]))
      {
        let intStr = '';
        while(i+1 < infix.length && isDigit(infix[i+1]))
        {
          intStr += infix[i];
          i++;
        }
        intStr += infix[i] + ' ';
        pf_str += intStr;
      }
      else if(isAlpha(infix[i]))
      {
        pf_str += infix[i] + ' '
      }
    }
  }

  while(opStack.length != 0)
  {
    pf_str += opStack.pop() + ' '
  }
  return pf_str;
};

function isOperator(c)
{
  if(c == '^' ||  c == '+' || c == '-' || c == '*' || c == '/') return true;
  return false;
};

function getPrecedence(op)
{
  if(op == '(' || op == ')') return 0;
  else if(op == '+' || op == '-') return 1;
  else if(op == '*' || op == '/') return 2;
  else if(op == '^') return 3;
};

function isOperand(c)
{
  return isDigit(c) || isAlpha(c);
};

function isDigit(c)
{
  if(c.charCodeAt(0) > 47 && c.charCodeAt(0) < 58) return true;
  return false;
};

function isAlpha(c)
{
 if(c.charCodeAt(0) > 96 && c.charCodeAt(0) < 123) return true;
 if(c.charCodeAt(0) > 64 && c.charCodeAt(0) < 91) return true;
 return false;
};

/*
function encodeHex2B64(hexStr)
{
  let result = '';
  let to_add;
  for(let i = hexStr.length-1; i > -1; i-=3)
  {
    to_add = hexStr[i];
    if(i-1 > -1) to_add = hexStr[i-1] + to_add;
    if(i-2 > -1) to_add = hexStr[i-2] + to_add;
    
  }
};
*/

//opens Lord of the Flies in a pdf to the given page number
function openLOTF(pg=0)
{
  window.open("https://englishcreek.weebly.com/uploads/6/9/7/2/6972564/g6_lord_of_the_flies_-_770l.pdf#page="+pg, '_blank');
};