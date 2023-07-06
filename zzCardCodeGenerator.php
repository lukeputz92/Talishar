<?php

  include './zzImageConverter.php';
  include './Libraries/Trie.php';

  //$jsonUrl = "https://raw.githubusercontent.com/the-fab-cube/flesh-and-blood-cards/v5.0.0/json/english/card.json";
  $jsonUrl = "https://raw.githubusercontent.com/the-fab-cube/flesh-and-blood-cards/dusk-till-dawn/json/english/card.json";
  $curl = curl_init();
  $headers = array(
    "Content-Type: application/json",
  );
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

  curl_setopt($curl, CURLOPT_URL, $jsonUrl);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $cardData = curl_exec($curl);
  curl_close($curl);


  $cardArray = json_decode($cardData);

  if(!is_dir("./GeneratedCode")) mkdir("./GeneratedCode", 777, true);

  $filename = "./GeneratedCode/GeneratedCardDictionaries.php";
  $handler = fopen($filename, "w");

  fwrite($handler, "<?php\r\n");

  GenerateFunction($cardArray, $handler, "CardType", "type", "AA");
  GenerateFunction($cardArray, $handler, "AttackValue", "attack");
  GenerateFunction($cardArray, $handler, "BlockValue", "block", "3");
  GenerateFunction($cardArray, $handler, "CardName", "name");
  GenerateFunction($cardArray, $handler, "PitchValue", "pitch", "1");
  GenerateFunction($cardArray, $handler, "CardCost", "cost", "0");
  GenerateFunction($cardArray, $handler, "CardSubtype", "subtype", "");
  GenerateFunction($cardArray, $handler, "CharacterHealth", "health", "20", true);//Also images
  GenerateFunction($cardArray, $handler, "Rarity", "rarity", "C");
  GenerateFunction($cardArray, $handler, "Is1H", "1H", "false", true);
  GenerateFunction($cardArray, $handler, "CardClass", "cardClass", "NONE");
  GenerateFunction($cardArray, $handler, "CardTalent", "cardTalent", "NONE");

  fwrite($handler, "?>");

  fclose($handler);

  function GenerateFunction(&$cardArray, $handler, $functionName, $propertyName, $defaultValue="", $sparse=false)
  {
    echo("<BR>" . $functionName . "<BR>");
    fwrite($handler, "function Generated" . $functionName . "(\$cardID) {\r\n");
    $originalSets = ["WTR", "ARC", "CRU", "MON", "ELE", "EVR", "UPR", "DYN", "OUT", "DVR", "RVD", "DTD", "LGS", "HER", "FAB"];
    $isString = true;
    $isBool = false;
    if($propertyName == "attack" || $propertyName == "block" || $propertyName == "pitch" || $propertyName == "cost" || $propertyName == "health" || $propertyName == "1H") $isString = false;
    if($propertyName == "1H") $isBool = true;
    fwrite($handler, "if(strlen(\$cardID) < 6) return " . ($isString ? "\"\"" : "0") . ";\r\n");
    fwrite($handler, "if(is_int(\$cardID)) return " . ($isString ? "\"\"" : "0") . ";\r\n");
    if($sparse) fwrite($handler, "switch(\$cardID) {\r\n");
    $trie = [];
    $cardsSeen = [];
    for($i=0; $i<count($cardArray); ++$i)
    {
      $cardRarity = "NA";
      $cardPrintings = [];
      for($j=0; $j<count($cardArray[$i]->printings); ++$j)
      {
        $cardRarity = $cardArray[$i]->printings[$j]->rarity;
        $cardID = $cardArray[$i]->printings[$j]->id;
        $set = substr($cardID, 0, 3);
        $cardNumber = substr($cardID, 3, 3);
        if(!in_array($set, $originalSets)) continue;
        if($set == "LGS" && $cardNumber < 156) continue;
        if($set == "HER" && $cardNumber < 84) continue;
        if($set == "FAB" && $cardNumber < 161) continue;
        if(isset($cardArray[$i]->printings[0]->double_sided_card_info) && !$cardArray[$i]->printings[0]->double_sided_card_info[0]->is_front && $cardArray[$i]->printings[0]->rarity != "T") { $cardNumber += 400; $cardID = $set . $cardNumber; }
        else {
          $duplicate = false;
          for($k=0; $k<count($cardPrintings); ++$k)
          {
            if($cardPrintings[$k] == $cardID) $duplicate = true;
          }
          for($k=0; $k<count($cardsSeen); ++$k)
          {
            if($cardsSeen[$k] == $cardID) $duplicate = true;
          }
          if($duplicate && $set != "DVR" && $set != "RVD") continue;
        }
        array_push($cardPrintings, $cardID);
        array_push($cardsSeen, $cardID);
        if($propertyName == "type") $data = MapType($cardArray[$i]);
        else if($propertyName == "attack") $data = $cardArray[$i]->power;
        else if($propertyName == "block")
        {
          $data = $cardArray[$i]->defense;
          if($data == "") $data = -1;
        }
        else if($propertyName == "name") $data = $cardArray[$i]->name;
        else if($propertyName == "pitch")
        {
          $data = $cardArray[$i]->pitch;
          if($data == "") $data = 0;
        }
        else if($propertyName == "cost")
        {
          $data = $cardArray[$i]->cost;
          if($data == "") $data = -1;
        }
        else if($propertyName == "health")
        {
          $data = $cardArray[$i]->health;
          CheckImage($cardID);
        }
        else if($propertyName == "rarity")
        {
          $data = $cardRarity;
        }
        else if($propertyName == "rarity")
        {
          $data = $cardRarity;
        }
        else if($propertyName == "subtype")
        {
          $data = "";
          for($k=0; $k<count($cardArray[$i]->types); ++$k)
          {
            $type = $cardArray[$i]->types[$k];
            if(!IsCardType($type) && !IsClass($type) && !IsTalent($type) && !IsHandedness($type))
            {
              if($data != "") $data .= ",";
              $data .= $type;
            }
          }
        }
        else if($propertyName == "1H")
        {
          $data = "false";
          for($k=0; $k<count($cardArray[$i]->types); ++$k)
          {
            $type = $cardArray[$i]->types[$k];
            if($type == "1H") $data = "true";
          }
        }
        else if($propertyName == "cardClass")
        {
          $data = "";
          for($k=0; $k<count($cardArray[$i]->types); ++$k)
          {
            $type = $cardArray[$i]->types[$k];
            if(IsClass($type))
            {
              if($data != "") $data .= ",";
              $data .= strtoupper($type);
            }
          }
        }
        else if($propertyName == "cardTalent")
        {
          $data = "";
          for($k=0; $k<count($cardArray[$i]->types); ++$k)
          {
            $type = $cardArray[$i]->types[$k];
            if(IsTalent($type))
            {
              if($data != "") $data .= ",";
              $data .= strtoupper($type);
            }
          }
        }
        if($isBool);
        else if(($isString == false && !is_numeric($data) && $data != "") || $data == "-" || $data == "*" || $data == "X") echo("Exception with property name " . $propertyName . " data " . $data . " card " . $cardID . "<BR>");
        if(($isBool && $data == "true") || ($data != "-" && $data != "" && $data != "*" && $data != $defaultValue))
        {
          if($sparse) fwrite($handler, "case \"" . $cardID . "\": return " . ($isString ? "\"$data\"" : $data) . ";\r\n");
          else AddToTrie($trie, $cardID, 0, $data);
        }
      }
    }
    if($sparse) fwrite($handler, "default: return " . ($isString ? "\"$defaultValue\"" : $defaultValue) . ";}\r\n");
    else TraverseTrie($trie, "", $handler, $isString, $defaultValue);

    fwrite($handler, "}\r\n\r\n");
  }

  function MapType($card)
  {
    $hasAction = false; $hasAttack = false;
    for($i=0; $i<count($card->types); ++$i)
    {
      if($card->types[$i] == "Action") $hasAction = true;
      else if($card->types[$i] == "Attack") $hasAttack = true;
      else if($card->types[$i] == "Defense") $hasDefense = true;
      else if($card->types[$i] == "Defense Reaction") return "DR";
      else if($card->types[$i] == "Attack Reaction") return "AR";
      else if($card->types[$i] == "Instant") return "I";
      else if($card->types[$i] == "Weapon") return "W";
      else if($card->types[$i] == "Hero") return "C";
      else if($card->types[$i] == "Equipment") return "E";
      else if($card->types[$i] == "Token") return "T";
      else if($card->types[$i] == "Resource") return "R";
      else if($card->types[$i] == "Mentor") return "M";
      else if($card->types[$i] == "Ally") return "ALLY";
      else if($card->types[$i] == "Demi-Hero") return "D";
    }
    if($hasAction && $hasAttack) return "AA";
    else if($hasAction) return "A";
    else
    {
      echo("No type found for " . $card->name);
    }
    return "-";
  }

  function IsCardType($term)
  {
    switch($term)
    {
      case "Action": case "Attack": case "Defense Reaction": case "Attack Reaction":
      case "Instant": case "Weapon": case "Hero": case "Equipment": case "Token":
      case "Resource": case "Mentor": return true;
      default: return false;
    }
  }

  function IsClass($term)
  {
    switch($term)
    {
      case "Generic": case "Warrior": case "Ninja": case "Brute": case "Guardian":
      case "Wizard": case "Mechanologist": case "Ranger": case "Runeblade":
      case "Illusionist": case "Assassin": return true;
      case "Shapeshifter": case "Merchant": case "Arbiter": return true;
      default: return false;
    }
  }

  function IsTalent($term)
  {
    switch($term)
    {
      case "Elemental": case "Light": case "Shadow": case "Draconic": return true;
      case "Ice": case "Lightning": case "Earth": return true;
      default: return false;
    }
  }

  function IsHandedness($term)
  {
    switch($term)
    {
      case "1H": case "2H": return true;
      default: return false;
    }
  }


?>
