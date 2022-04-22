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