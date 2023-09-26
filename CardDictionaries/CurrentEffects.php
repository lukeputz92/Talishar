<?php

  function TCCEffectAttackModifier($cardID)
  {
    $idArr = explode(",", $cardID);
    $cardID = $idArr[0];
    switch($cardID) {
      case "TCC037": return 6;
      case "TCC038": return 5;
      case "TCC042": return 5;
      case "TCC043": return 4;
      case "TCC057": return $idArr[1];
      case "TCC083": return 1;
      case "TCC086": case "TCC094": return 1;
      case "TCC105": return 1;
      case "TCC409": return 1;
      default: return 0;
    }
  }

  function TCCCombatEffectActive($cardID, $attackID)
  {
    global $mainPlayer;
    $idArr = explode(",", $cardID);
    $cardID = $idArr[0];
    switch($cardID) {
      case "TCC035": return true;
      case "TCC037": case "TCC038": case "TCC042": case "TCC043": return ClassContains($attackID, "GUARDIAN", $mainPlayer) && CardType($attackID) == "AA";
      case "TCC057": return true;
      case "TCC083": return true;
      case "TCC086": case "TCC094": return CardName($attackID) == "Crouching Tiger";
      case "TCC105": return true;
      case "EVO155": case "EVO156": case "EVO157": return true;
      case "TCC409": return true;
      default: return false;
    }
  }

  function EVOEffectAttackModifier($cardID)
  {
    $idArr = explode(",", $cardID);
    $cardID = $idArr[0];
    switch($cardID) {
      case "EVO009": return 1;
      case "EVO016": return 1;
      case "EVO126": case "EVO127": case "EVO128": return 1;
      case "EVO140": return 3;
      case "EVO155": case "EVO156": case "EVO157": return 2;
      case "EVO192": case "EVO193": case "EVO194":
      case "EVO195": case "EVO196": case "EVO197": return 1;
      case "EVO222": case "EVO225": case "EVO228": return 4;
      case "EVO223": case "EVO226": case "EVO229": return 3;
      case "EVO224": case "EVO227": case "EVO230": return 2;
      case "EVO436": return 1;
      default: return 0;
    }
  }

  function EVOCombatEffectActive($cardID, $attackID)
  {
    global $mainPlayer, $combatChainState, $CCS_IsBoosted;
    $idArr = explode(",", $cardID);
    $cardID = $idArr[0];
    switch($cardID) {
      case "EVO009": return true;
      case "EVO016": return CardType($attackID) == "AA" && ClassContains($attackID, "MECHANOLOGIST", $mainPlayer);
      case "EVO102": case "EVO103": case "EVO104": return true;
      case "EVO126": case "EVO127": case "EVO128": return true;
      case "EVO140": return true;
      case "EVO155": case "EVO156": case "EVO157": return true;
      case "EVO192": case "EVO193": case "EVO194":
      case "EVO195": case "EVO196": case "EVO197": return true;
      case "EVO222": case "EVO223": case "EVO224":
      case "EVO225": case "EVO226": case "EVO227":
      case "EVO228": case "EVO229": case "EVO230": return $combatChainState[$CCS_IsBoosted];
      case "EVO434": return CardType($attackID) == "W";
      case "EVO436": return CardType($attackID) == "W";
      default: return false;
    }
  }
?>
