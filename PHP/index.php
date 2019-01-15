<?php 
  include 'menu.txt'; 
  
  $connection = mysqli_connect("mysql.itn.liu.se","lego","","lego");
	if (!$connection) {
		die('MySQL connection error');
  }
  
  
  
  error_reporting(E_ALL);
  ini_set("display_errors", 1);



?>



<main>

  <!--<button onclick="topFunction()" id="myBtn" title="Go to top">Top</button> -->

  <?php

  
  //if a specific set is set in the get variable, only that set is shown with details of set. 
  if(isset($_GET['setID'])){
    $SetID = $_GET['setID'];
    $setLoaded = true;
    ?>
    <div id="scrollspyBtns">
      <button onclick="scrollToElement('csScroll')" class="scrollspyBtn">Complete Set</button>
      <!-- <button onclick="scrollToElement('csPartsScroll')" class="scrollspySubBtn">Parts</button>
      <button onclick="scrollToElement('csMinifigsScroll')" class="scrollspySubBtn">Minifigs</button> -->
      <button onclick="scrollToElement('opScroll')" class="scrollspyBtn">Piecees you own for this set</button>
      <!-- <button onclick="scrollToElement('opPartsScroll')" class="scrollspySubBtn">Parts</button>
      <button onclick="scrollToElement('opMinifigsScroll')" class="scrollspySubBtn">Minifigs</button>-->
      <button onclick="scrollToElement('mpScroll')" class="scrollspyBtn">Piecees you miss for this set</button>
      <!--<button onclick="scrollToElement('mpPartsScroll')" class="scrollspySubBtn">Parts</button>
      <button onclick="scrollToElement('mpMinifigsScroll')" class="scrollspySubBtn">Minifigs</button> -->
      <button onclick="scrollToElement('wcScroll')" class="scrollspyBtn" id="scrollBtn4">Piecees you own for this set but in the wrong color</button>
      <!-- <button onclick="scrollToElement('wcPartsScroll')" class="scrollspySubBtn">Parts</button> -->
    </div>
    <?php

    //ask for set name.
    $query = mysqli_query($connection, "SELECT sets.Setname FROM sets WHERE sets.SetID='$SetID'");
    $no_image = false;

    $firstRow = mysqli_fetch_array($query);
    $setName = $firstRow['Setname'];

    $prefix = "http://www.itn.liu.se/~stegu76/img.bricklink.com/";

    $imagesearch = mysqli_query($connection, "SELECT * FROM images, sets WHERE ItemTypeID='S' AND SetID='$SetID' AND images.ItemID=sets.SetID");
    
    $imageinfo = mysqli_fetch_array($imagesearch);
    if($imageinfo['has_largejpg']) { 
      $filename = "SL/$SetID.jpg";
    } 
    else if($imageinfo['has_largegif']) { 
      $filename = "SL/$SetID.gif";
    } 
    else { 
      $no_image = true;
    }

    if(!$no_image){
      $picSource = $prefix . $filename;
    }
    else{
      $picSource = "../IMAGES/No_Image.png";
    }

    print("<div id='fullSet'>");
    //prints info about set
    print("<h2 class='setHeader'>Name: $setName</h2>");
    print("<h2 class='setHeader'>Set: $SetID</h2>");
    print("<img src='$picSource' alt='picture of legoset'/>");
    print("</div>");

    //ask server for set parts and store in array
    $query = mysqli_query($connection,"SELECT inventory.ItemID, inventory.ColorID, inventory.Extra, inventory.Quantity, parts.Partname, colors.Colorname FROM inventory, parts, colors WHERE inventory.SetID='$SetID' AND
    inventory.ColorID=colors.ColorID AND inventory.ItemID=parts.PartID");

    $index = 0;
    while($row = mysqli_fetch_array($query)) {

      if($row['Extra'] == 'N'){
        $completeSetParts[$index]['ItemID'] = $row['ItemID'];
        $completeSetParts[$index]['ColorID'] = $row['ColorID'];
        $completeSetParts[$index]['Quantity'] = $row['Quantity'];
        $completeSetParts[$index]['Partname'] = $row['Partname'];
        $completeSetParts[$index]['Colorname'] = $row['Colorname'];
        $index++;
      }
    }

    //ask for minifigs and stores in completeset array
    $query = mysqli_query($connection, "SELECT minifigs.Minifigname, inventory.ItemID, inventory.Quantity FROM minifigs, inventory WHERE inventory.SetID='$SetID' AND inventory.ItemID=minifigs.MinifigID");

    $minifigIndex = 0;
    while($row = mysqli_fetch_array($query)) {
      $completeSetMinifigs[$minifigIndex]['Name'] = $row['Minifigname'];
      $completeSetMinifigs[$minifigIndex]['ItemID'] = $row['ItemID'];
      $completeSetMinifigs[$minifigIndex]['Quantity'] = $row['Quantity'];

      $minifigIndex++;
    }

    print("<button id='csScroll' class='accordion'>Complete Set</button>\n");
    print("<div class='panel'>\n");
    print("<table>\n");
    if(!empty($completeSetParts)){
      print("<tr><th id='csPartsScroll' class='topTableHead' colspan='5'>Parts:</th></tr>\n");
      print("<tr><th>Picture</th><th>Quantity</th><th>Part Name</th> <th>Color</th> <th>Part ID</th> </tr>\n");
      
      //print part rows
      for($j = 0; $j < $index; $j++){
        $no_image = false;
        $quantity = $completeSetParts[$j]['Quantity'];
        $partName = $completeSetParts[$j]['Partname'];
        $colorName = $completeSetParts[$j]['Colorname'];
        $colorID = $completeSetParts[$j]['ColorID'];
        $partID = $completeSetParts[$j]['ItemID'];
        //function for finding image url
        $imagesearch = mysqli_query($connection, "SELECT * FROM images, parts WHERE ItemTypeID='P' AND parts.PartID='$partID' AND images.ColorID='$colorID' AND images.ItemID=parts.PartID");
          
        $imageinfo = mysqli_fetch_array($imagesearch);
        if($imageinfo['has_jpg']) { 
          $filename = "P/$colorID/$partID.jpg";
        } 
        else if($imageinfo['has_gif']) { 
          $filename = "P/$colorID/$partID.gif";
        } 
        else { 
          $no_image = true;
        }
  
        if(!$no_image){
          $picSource = $prefix . $filename;
        }
        else{
          $picSource = "../IMAGES/No_Image.png";
        }

        print("<tr><td><img src='$picSource' alt='Img missing'></td><td class='centerText'>$quantity</td><td class='nameTd'>$partName</td> <td class='centerText'>$colorName</td> <td class='centerTd'><a href='my_parts.php?partID=$partID&colorID=$colorID&setID=$SetID&setName=$setName'>$partID</a></td></tr>\n");
      }
    }
    else{
      print("<p class='centerText'>Set exists in database but no information about this set's pieces exist!!!</p>\n");
      $setLoaded = false;
    }
    //printing minifig rows
    if(!empty($completeSetMinifigs[0])){
      print("<tr><th id='csMinifigsScroll' class='topTableHead' colspan='5'>Minifigs:</th></tr>\n");
      print("<tr><th colspan='1'>Image</th><th colspan='1'>Quantity</th><th colspan='2'>Name</th><th colspan='1'>Minifig ID</th></tr>\n");

      for($j = 0; $j < $minifigIndex; $j++){
        $no_image = false;
        $name = $completeSetMinifigs[$j]['Name'];
        $minifigID = $completeSetMinifigs[$j]['ItemID'];
        $quantity = $completeSetMinifigs[$j]['Quantity'];
        //function for finding image url

        $imagesearch = mysqli_query($connection, "SELECT * FROM images WHERE images.ItemID='$minifigID'");
          
        $imageinfo = mysqli_fetch_array($imagesearch);
        if($imageinfo['has_jpg']) { 
          $filename = "M/$minifigID.jpg";
        } 
        else if($imageinfo['has_gif']) { 
          $filename = "M/$minifigID.gif";
        } 
        else { 
          $no_image = true;
        }
  
        if(!$no_image){
          $picSource = $prefix . $filename;
        }
        else{
          $picSource = "../IMAGES/No_Image.png";
        }

        print("<tr><td colspan='1'><img src='$picSource' alt='Img missing'></td><td colspan='1' class='centerText'>$quantity</td><td colspan='2' class='centerText nameTd'>$name</td><td class='centerText' colspan='1'>$minifigID</td></tr>\n");
      }
    }

    print("</table>\n");
    print("</div>\n");

    // calculate the pieces this person owns of this set and store the results in an array
    for($j = 0; $j < $index; $j++){
      $partID = $completeSetParts[$j]['ItemID']; //first value is 11477
      $colorID = $completeSetParts[$j]['ColorID'];//first value is 156
      $colorName = $completeSetParts[$j]['Colorname'];
      $partName = $completeSetParts[$j]['Partname'];
      
      $result = mysqli_query($connection, 
      "SELECT collection.Quantity setQuantity, sets.SetID, inventory.Quantity partQuantity, parts.PartID, colors.ColorID 
      FROM sets, collection, parts, colors, inventory 
      WHERE inventory.ItemID=parts.PartID AND sets.SetID=collection.SetID AND sets.SetID=inventory.SetID 
      AND inventory.ColorID=colors.ColorID AND parts.PartID='$partID' AND colors.ColorID='$colorID' ORDER BY parts.PartID");

      $ownedParts = 0;
      while($row = mysqli_fetch_array($result)){
        $ownedParts += $row['setQuantity'] * $row['partQuantity'];
      }

      if($ownedParts < $completeSetParts[$j]['Quantity']){
        //creates array for missing pieces before printning.
        $missingPartsArray[$j]['Quantity'] = $ownedParts;
        $missingPartsArray[$j]['PartID'] = $partID;
        $missingPartsArray[$j]['Partname'] = $partName;
        $missingPartsArray[$j]['ColorID'] = $colorID;
        $missingPartsArray[$j]['Colorname'] = $colorName;
      }

      else{
        $ownedPartsArray[$j]['PartID'] = $partID;
        $ownedPartsArray[$j]['Quantity'] = $ownedParts;
        $ownedPartsArray[$j]['Partname'] = $partName;
        $ownedPartsArray[$j]['ColorID'] = $colorID;
        $ownedPartsArray[$j]['Colorname'] = $colorName;
      }
    }
    
    $ownedMinifigs = 0;
    //calculate owned minifigures and store in two different arrays
    for($j = 0; $j < $minifigIndex; $j++){
      $minifigName = $completeSetMinifigs[$j]['Name'];
      $minifigID = $completeSetMinifigs[$j]['ItemID'];

      $result = mysqli_query($connection, 
      "SELECT collection.Quantity setQuantity, inventory.ItemID, minifigs.Minifigname, inventory.Quantity partQuantity FROM inventory, collection, minifigs 
      WHERE inventory.ItemID='$minifigID' AND inventory.SetID=collection.SetID AND inventory.ItemID=minifigs.MinifigID");

      while($row = mysqli_fetch_array($result)){
        $ownedMinifigs += $row['setQuantity'] * $row['partQuantity'];
      }

      if ($ownedMinifigs < $completeSetMinifigs[$j]['Quantity']){
        $missingMinifigs[$j]['Name'] = $minifigName;
        $missingMinifigs[$j]['ItemID'] = $minifigID;
        $missingMinifigs[$j]['Quantity'] = $ownedMinifigs; 
      }

      else{
        $ownedMinifigsArray[$j]['Name'] = $minifigName;
        $ownedMinifigsArray[$j]['ItemID'] = $minifigID;
        $ownedMinifigsArray[$j]['Quantity'] = $ownedMinifigs; 
        }
      

    }

    //print owned pieces of this set from array
    print("<button id='opScroll' class='accordion'>Pieces you own of this set</button>\n");
    print("<div class='panel'>\n");
    print("<table>\n");

    if(empty($missingPartsArray)){
      print("<p class='centerText' >You own <b>EVERY</b> piece of this set.</p>\n");
    }
    else if(!empty($ownedPartsArray)){
      print("<tr><th id='opPartsScroll' class='topTableHead' colspan='6'>Parts:</th></tr>\n");
      print("<tr><th>Picture</th><th>Needed</th><th>Have</th><th>Part Name</th> <th>Color</th> <th>Part ID</th></tr>\n");

      for($j = 0; $j < $index; $j++){
        $no_image = false;
        if(array_key_exists($j, $ownedPartsArray)){
          $quantity = $ownedPartsArray[$j]['Quantity'];
          $partName = $ownedPartsArray[$j]['Partname'];
          $colorName = $ownedPartsArray[$j]['Colorname'];
          $partID = $ownedPartsArray[$j]['PartID'];
          $colorID = $completeSetParts[$j]['ColorID'];
          $piecesNeeded = $completeSetParts[$j]['Quantity'];
          //function for finding image url
          $imagesearch = mysqli_query($connection, "SELECT * FROM images, parts WHERE ItemTypeID='P' AND parts.PartID='$partID' AND images.ColorID='$colorID' AND images.ItemID=parts.PartID");
          
          $imageinfo = mysqli_fetch_array($imagesearch);
          if($imageinfo['has_jpg']) { 
            $filename = "P/$colorID/$partID.jpg";
          } 
          else if($imageinfo['has_gif']) { 
            $filename = "P/$colorID/$partID.gif";
          } 
          else { 
            $no_image = true;
          }
    
          if(!$no_image){
            $picSource = $prefix . $filename;
          }
          else{
            $picSource = "../IMAGES/No_Image.png";
          }

          if($quantity < $completeSetParts[$j]['Quantity']){
            continue;
          }
          else{
            print("<tr><td><img src='$picSource' alt='Img missing'></td><td class='centerText'>$piecesNeeded</td><td class='centerText'>$quantity</td><td class='nameTd'>$partName</td> <td class='centerText'>$colorName</td><td class='centerTd'><a href='my_parts.php?partID=$partID&colorID=$colorID&setID=$SetID&setName=$setName'>$partID</a></td></tr>\n");
          }
        }
      }
    }
    else{
      print("<p class='centerText' >You own <b>NO</b> pieces of this set.</p>\n");
    }
    //print owned minifigures
    if(!empty($ownedMinifigsArray[0])){
      print("<tr><th id='opMinifigsScroll' class='topTableHead' colspan='6'>Minifigs:</th></tr>\n");
      print("<tr><th colspan='1'>Image</th><th>Needed</th><th colspan='1'>Have</th><th colspan='2'>Name</th><th colspan='1'>Minifig ID</th></tr>\n");

      for($j = 0; $j < $minifigIndex; $j++){
        $no_image = false;
        $name = $ownedMinifigsArray[$j]['Name'];
        $minifigID = $ownedMinifigsArray[$j]['ItemID'];
        $owned = $ownedMinifigsArray[$j]['Quantity'];
        $needed = $completeSetMinifigs[$j]['Quantity'];
        
        //function for finding image url
        $imagesearch = mysqli_query($connection, "SELECT * FROM images WHERE images.ItemID='$minifigID'");
          
        $imageinfo = mysqli_fetch_array($imagesearch);
        if($imageinfo['has_jpg']) { 
          $filename = "M/$minifigID.jpg";
        } 
        else if($imageinfo['has_gif']) { 
          $filename = "M/$minifigID.gif";
        } 
        else { 
          $no_image = true;
        }
  
        if(!$no_image){
          $picSource = $prefix . $filename;
        }
        else{
          $picSource = "../IMAGES/No_Image.png";
        }

        print("<tr><td colspan='1'><img src='$picSource' alt='Img missing'><td class='centerText'>$needed</td><td class='centerText' colspan='1'>$owned</td></td><td colspan='2' class='centerText'>$name</td><td class='centerText' colspan='1'>$minifigID</td></tr>\n");
      }
    }
    
    print("</table>\n");
    print("</div>\n");

    //print missing pieces of this set from array
    print("<button id='mpScroll' class='accordion'>Pieces you miss for this set</button>\n");
    print("<div class='panel'>\n");
    print("<table>\n");

    

    if(!empty($missingPartsArray)){
      print("<tr><th id='mpPartsScroll' class='topTableHead' colspan='6'>Parts:</th></tr>\n");
      print("<tr><th>Picture</th><th>Needed</th><th>Have</th><th>Part Name</th> <th>Color</th> <th>Part ID</th></tr>\n");

      for($j = 0; $j < $index; $j++){
        $no_image = false;
        if(array_key_exists($j, $missingPartsArray)){
          $quantity = $missingPartsArray[$j]['Quantity'];
          $partName = $missingPartsArray[$j]['Partname'];
          $colorName = $missingPartsArray[$j]['Colorname'];
          $partID = $missingPartsArray[$j]['PartID'];
          $colorID = $missingPartsArray[$j]['ColorID'];
          $piecesNeeded = $completeSetParts[$j]['Quantity'];
          //function for finding image url
          $imagesearch = mysqli_query($connection, "SELECT * FROM images, parts WHERE ItemTypeID='P' AND parts.PartID='$partID' AND images.ColorID='$colorID' AND images.ItemID=parts.PartID");
            
          $imageinfo = mysqli_fetch_array($imagesearch);
          if($imageinfo['has_jpg']) { 
            $filename = "P/$colorID/$partID.jpg";
          } 
          else if($imageinfo['has_gif']) { 
            $filename = "P/$colorID/$partID.gif";
          } 
          else { 
            $no_image = true;
          }
    
          if(!$no_image){
            $picSource = $prefix . $filename;
          }
          else{
            $picSource = "../IMAGES/No_Image.png";
          }

          print("<tr><td><img src='$picSource' alt='Img missing'></td><td class='centerText'>$piecesNeeded</td><td class='centerText'>$quantity</td><td class='nameTd'>$partName</td> <td class='centerText'>$colorName</td><td class='centerTd'><a href='my_parts.php?partID=$partID&colorID=$colorID&setID=$SetID&setName=$setName'>$partID</a></td></tr>\n");
        }
      }
    }
    else{
      print("<p class='centerText' >You own <b>EVERY</b> piece of this set.</p>\n");
    }
  //prints missing minifigs
  if(!empty($missingMinifigs[0])){
    print("<tr><th id='mpMinifigsScroll' class='topTableHead' colspan='6'>Minifigs:</th></tr>\n");
    print("<tr><th colspan='1'>Image</th><th>Needed</th><th colspan='1'>Have</th><th colspan='2'>Name</th><th colspan='1'>Minifig ID</th></tr>\n");

    for($j = 0; $j < $minifigIndex; $j++){
      $no_image = false;
      $name = $missingMinifigs[$j]['Name'];
      $minifigID = $missingMinifigs[$j]['ItemID'];
      $owned = $missingMinifigs[$j]['Quantity'];
      $needed = $completeSetMinifigs[$j]['Quantity'];
      
      //function for finding image url
      $imagesearch = mysqli_query($connection, "SELECT * FROM images WHERE images.ItemID='$minifigID'");
        
      $imageinfo = mysqli_fetch_array($imagesearch);
      if($imageinfo['has_jpg']) { 
        $filename = "M/$minifigID.jpg";
      } 
      else if($imageinfo['has_gif']) { 
        $filename = "M/$minifigID.gif";
      } 
      else { 
        $no_image = true;
      }

      if(!$no_image){
        $picSource = $prefix . $filename;
      }
      else{
        $picSource = "../IMAGES/No_Image.png";
      }

      print("<tr><td colspan='1'><img src='$picSource' alt='Img missing'></td><td class='centerText'>$needed</td><td class='centerText' colspan='1'>$owned</td><td colspan='2' class='centerText'>$name</td><td colspan='1' class='centerText'>$minifigID</td></tr>\n");
    }
  }

  print("</table>\n");
  print("</div>\n");

    //stores different color of piece in an array
    for($j = 0; $j < $index; $j++){
      if(!empty($missingPartsArray)){
        if(array_key_exists($j, $missingPartsArray)){
          $partID = $missingPartsArray[$j]['PartID']; 
          $colorID = $missingPartsArray[$j]['ColorID'];
          $colorName = $missingPartsArray[$j]['Colorname'];
          $partName = $missingPartsArray[$j]['Partname'];
          
          $result = mysqli_query($connection, 
          "SELECT collection.Quantity setQuantity, sets.SetID, inventory.Quantity partQuantity, parts.PartID, colors.ColorID, parts.Partname, colors.Colorname 
          FROM sets, collection, parts, colors, inventory 
          WHERE inventory.ItemID=parts.PartID AND sets.SetID=collection.SetID AND sets.SetID=inventory.SetID 
          AND inventory.ColorID=colors.ColorID AND parts.PartID='$partID' ORDER BY parts.PartID LIMIT 2");

          while($row = mysqli_fetch_array($result)){
            for ($k=0; $k < $index; $k++) { 
              if($partID == $completeSetParts[$k]['ItemID'] && $row['partQuantity'] >= $completeSetParts[$k]['Quantity'] && $row['ColorID'] != $completeSetParts[$k]['ColorID']){
                $differentColor[$j]['PartID'] = $partID;
                $differentColor[$j]['ColorID'] = $row['ColorID'];
                $differentColor[$j]['Quantity'] = $row['partQuantity'];
                $differentColor[$j]['Partname'] = $row['Partname'];
                $differentColor[$j]['Colorname'] = $row['Colorname'];
                break;
              }
            }
          }
        }
      }
      else{
        break;
      }      
    }

    //searches for all pieces of same color and stores
    for($j = 0; $j < $index; $j++){
      if(!empty($differentColor)){
        if(array_key_exists($j, $differentColor)){
          $partID = $differentColor[$j]['PartID']; 
          $colorID = $differentColor[$j]['ColorID'];
          $colorName = $differentColor[$j]['Colorname'];
          $partName = $differentColor[$j]['Partname'];
          
          $result = mysqli_query($connection, 
          "SELECT collection.Quantity setQuantity, sets.SetID, inventory.Quantity partQuantity, parts.PartID, colors.ColorID 
          FROM sets, collection, parts, colors, inventory 
          WHERE inventory.ItemID=parts.PartID AND sets.SetID=collection.SetID AND sets.SetID=inventory.SetID 
          AND inventory.ColorID=colors.ColorID AND parts.PartID='$partID' AND colors.ColorID='$colorID' ORDER BY parts.PartID");

          $ownedParts = 0;
          while($row = mysqli_fetch_array($result)){
            $ownedParts += $row['setQuantity'] * $row['partQuantity'];
          }

          $differentColor[$j]['Quantity'] = $ownedParts;
        }
      }
      else{
        break;
      }
    }



    print("<button id='wcScroll' class='accordion'>Replacements for certain parts you miss</button>\n");
    print("<div class='panel'>\n");
    print("<table>\n");
    if(!empty($differentColor)){
      print("<tr><th id='wcPartsScroll' class='topTableHead' colspan='7'>Parts:</th></tr>\n");
      print("<tr><th>Picture</th><th>Needed</th><th>Have</th><th>Part Name</th> <th>Needed Color</th><th>Owned Color</th> <th>Part ID</th></tr>\n");

      for($j = 0; $j < $index; $j++){
        $no_image = false;
        if(!empty($differentColor)){
          if(array_key_exists($j, $differentColor)){
            $quantity = $differentColor[$j]['Quantity'];
            $partName = $differentColor[$j]['Partname'];
            $colorName = $differentColor[$j]['Colorname'];
            $colorNameNeeded = $completeSetParts[$j]['Colorname'];
            $colorID = $differentColor[$j]['ColorID'];
            $partID = $differentColor[$j]['PartID'];
            $piecesNeeded = $completeSetParts[$j]['Quantity'];

            $imagesearch = mysqli_query($connection, "SELECT * FROM images, parts WHERE ItemTypeID='P' AND parts.PartID='$partID' AND images.ColorID='$colorID' AND images.ItemID=parts.PartID");
            
            $imageinfo = mysqli_fetch_array($imagesearch);
            if($imageinfo['has_jpg']) { 
              $filename = "P/$colorID/$partID.jpg";
            } 
            else if($imageinfo['has_gif']) { 
              $filename = "P/$colorID/$partID.gif";
            } 
            else { 
              $no_image = true;
            }
      
            if(!$no_image){
              $picSource = $prefix . $filename;
            }
            else{
              $picSource = "../IMAGES/No_Image.png";
            }

            print("<tr><td><img src='$picSource' alt='Img missing'></td><td class='centerText'>$piecesNeeded</td><td class='centerText'>$quantity</td><td class='nameTd'>$partName</td><td class='centerText'>$colorNameNeeded</td><td class='centerText'>$colorName</td><td class='centerTd'><a href='my_parts.php?partID=$partID&colorID=$colorID&setID=$SetID&setName=$setName'>$partID</a></td></tr>\n");
          }
        }
        else{
          break;
        }
      }
    }
    else{
      print("<p class='centerText' ><b>NO</b> replacements found</p>\n");
    }
    print("</table>\n");
    print("</div>\n");
    if(!$setLoaded){
      print("<p class='centerTd'>This set exist in database but no information about this set's pieces exists!!!</p>");
      print("<script src='../JS/notLoadingSet.js'></script>\n");
    }
  }

  


  else{

    //pagenation
    if (isset($_GET["page"])) { $page  = $_GET["page"]; } 
    else { $page=1; };
    $limit = 18;
    $start_from = ($page-1) * $limit;

    $query = mysqli_query($connection, "SELECT sets.SetID, sets.Setname FROM sets, collection WHERE collection.SetID=sets.SetID LIMIT $start_from,".$limit);



    while($row = mysqli_fetch_array($query)) {
      $no_image = false;
      $SetID = $row['SetID'];
      $setName = $row['Setname'];
      $prefix = "http://www.itn.liu.se/~stegu76/img.bricklink.com/";
      
      $imagesearch = mysqli_query($connection, "SELECT * FROM images, sets WHERE ItemTypeID='S' AND SetID='$SetID' AND images.ItemID=sets.SetID");
      
      $imageinfo = mysqli_fetch_array($imagesearch);
      if($imageinfo['has_largejpg']) { 
        $filename = "SL/$SetID.jpg";
      } 
      else if($imageinfo['has_largegif']) { 
        $filename = "SL/$SetID.gif";
      } 
      else { 
        $no_image = true;
      }

      if(!$no_image){
        $picSource = $prefix . $filename;
      }
      else{
        $picSource = "../IMAGES/No_Image.png";
      }
      
      print("<div class='setOverview'>\n");
      print("<p>Set: $SetID</p> <p>$setName</p>\n");
      print("<img class='setPic' src=\"$picSource\" alt='Picture of Set' />\n");
      print("<a  class='readMore'href='index.php?setID=$SetID'><p>Show Set</p></a>\n");
      print('</div>');
      
    }

    $query2 = mysqli_query($connection, "SELECT COUNT(sets.SetID) AS total FROM sets, collection WHERE sets.SetID=collection.SetID");
      $row = $query2->fetch_assoc();
      $total_pages = ceil($row["total"] / $limit); // calculate total pages with results
      $targetpage = "index.php";
      $pagination = "";
      $adjacents = 2;
      $prev = $page - 1;                          //previous page is page - 1
      $next = $page + 1;                          //next page is page + 1
      $lastpage = $total_pages;      //lastpage is = total pages / items per page, rounded up.
      $lpm1 = $lastpage - 1;                      //last page minus 

    
    $pagination = "";
    if($lastpage > 1)
    {   
        $pagination .= "<div class=\"pagination\">";
        //previous button
        if ($page > 1) 
            $pagination.= "<a href=\"$targetpage?page=$prev\">previous</a>";
        else
            $pagination.= "<span class=\"disabled\">previous</span>"; 

        //pages 
        if ($lastpage < 7 + ($adjacents * 2))    //not enough pages to bother breaking it up
        {   
            for ($counter = 1; $counter <= $lastpage; $counter++)
            {
                if ($counter == $page)
                    $pagination.= "<span class=\"current\">$counter</span>";
                else
                    $pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";                 
            }
        }
        elseif($lastpage > 5 + ($adjacents * 2)) //enough pages to hide some
        {
            //close to beginning; only hide later pages
            if($page < 1 + ($adjacents * 2))     
            {
                for ($counter = 1; $counter < 2 + ($adjacents * 2); $counter++)
                {
                    if ($counter == $page)
                        $pagination.= "<span class=\"current\">$counter</span>";
                    else
                        $pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";                 
                }
                $pagination.= "...";
                $pagination.= "<a href=\"$targetpage?page=$lpm1\">$lpm1</a>";
                $pagination.= "<a href=\"$targetpage?page=$lastpage\">$lastpage</a>";       
            }
            //in middle; hide some front and some back
            elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
            {
                $pagination.= "<a href=\"$targetpage?page=1\">1</a>";
                //$pagination.= "<a href=\"$targetpage?page=2\">2</a>";
                $pagination.= "...";
                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                {
                    if ($counter == $page)
                        $pagination.= "<span class=\"current\">$counter</span>";
                    else
                        $pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";                 
                }
                $pagination.= "...";
                //$pagination.= "<a href=\"$targetpage?page=$lpm1\">$lpm1</a>";
                $pagination.= "<a href=\"$targetpage?page=$lastpage\">$lastpage</a>";       
            }
            //close to end; only hide early pages
            else
            {
                $pagination.= "<a href=\"$targetpage?page=1\">1</a>";
                //$pagination.= "<a href=\"$targetpage?page=2\">2</a>";
                $pagination.= "...";
                for ($counter = $lastpage - ($adjacents * 2); $counter <= $lastpage; $counter++)
                {
                    if ($counter == $page)
                        $pagination.= "<span class=\"current\">$counter</span>";
                    else
                        $pagination.= "<a href=\"$targetpage?page=$counter\">$counter</a>";                 
                }
            }
        }

        //next button
        if ($page < $counter - 1) 
            $pagination.= "<a href=\"$targetpage?page=$next\">next</a>";
        else
            $pagination.= "<span class=\"disabled\">next</span>";
        $pagination.= "</div>\n";     
    }
    echo " $pagination";
    echo ("Visar ".$limit." av ".$row["total"]);

  }
  ?>

</main>
<script src="../JS/accordion.js"></script>
<script src="../JS/scrollspy.js"></script>

</body>

</html>





<!-- <div>Font made from <a href="http://www.onlinewebfonts.com">oNline Web Fonts</a>is licensed by CC BY 3.0</div> -->