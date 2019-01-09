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
  <?php
  //if a specific set is set in the get variable, only that set is shown with details of set. 
  if(isset($_GET['setID'])){
    $SetID = $_GET['setID'];

    $query = mysqli_query($connection, "SELECT sets.SetID, sets.Setname, inventory.Quantity, inventory.ItemID, inventory.ColorID, colors.Colorname, parts.PartID, parts.Partname FROM sets, inventory, parts, colors WHERE sets.SetID='$SetID' AND inventory.SetID='$SetID' AND inventory.ItemID=parts.PartID AND colors.ColorID=inventory.ColorID");


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
      $filename = "noimage_small.png";
    }
      
    $picSource = $prefix . $filename;

    print("<div id='fullSet'>");
    
    print("<h2 class='setHeader'>Name: $setName</h2>");
    print("<h2 class='setHeader'>Set: $SetID</h2>");
    print("<img src='$picSource'/>");
    print("</div>");

    //ask server for set parts and store in array
    $query = mysqli_query($connection,"SELECT inventory.ItemID, inventory.ColorID, inventory.Quantity, parts.Partname, colors.Colorname FROM inventory, parts, colors WHERE inventory.SetID='$SetID' AND
    inventory.ColorID=colors.ColorID AND inventory.ItemID=parts.PartID");

    $index = 0;
    while($row = mysqli_fetch_array($query)) {
      $completeSetParts[$index]['ItemID'] = $row['ItemID'];
      $completeSetParts[$index]['ColorID'] = $row['ColorID'];
      $completeSetParts[$index]['Quantity'] = $row['Quantity'];
      $completeSetParts[$index]['Partname'] = $row['Partname'];
      $completeSetParts[$index]['Colorname'] = $row['Colorname'];
      $index++;
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

    print("<button class='accordion'>Complete Set</button>");
    print("<div class='panel'>\n");
    print("<table>\n<tr>\n");
    print("<th>Picture</th><th>Quantity</th><th>Part Name</th> <th>Color</th> <th>Part ID</th> </tr>\n");
    
    //print part rows
    for($j = 0; $j < $index; $j++){
      
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
        $filename = "noimage_small.png";
      }
  
      $picSource = $prefix . $filename;

      print("<tr><td><img src='$picSource' alt='Img missing'></td><td class='centerTd'>$quantity</td><td>$partName</td> <td class='centerTd'>$colorName</td> <td class='centerTd'>$partID</td></tr>");
    }
    
    //printing minifig rows
    if(!empty($completeSetMinifigs[0])){
      print("<tr><th colspan='5'>Minifigs:</th></tr>");
      print("<tr><th colspan='1'>Image</th><th colspan='1'>Quantity</th><th colspan='2'>Name</th><th colspan='1'>Minifig ID</th></tr>");

      for($j = 0; $j < $minifigIndex; $j++){
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
            $filename = "noimage_small.png";
        }
    
        $picSource = $prefix . $filename;

        print("<tr><td colspan='1'><img src='$picSource' alt='Img missing'><td colspan='1'>$quantity</td></td><td colspan='2' class='centerTd'>$name</td><td colspan='1'>$minifigID</td></tr>");
      }
    }

    print("</table>");
    print("</div>");

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

      $ownedPartsArray[$j]['Quantity'] = $ownedParts;
      $ownedPartsArray[$j]['PartID'] = $partID;
      $ownedPartsArray[$j]['Partname'] = $partName;
      $ownedPartsArray[$j]['ColorID'] = $colorID;
      $ownedPartsArray[$j]['Colorname'] = $colorName;
      
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
    print("<button class='accordion'>Pieces you own of this set</button>");
    print("<div class='panel'>\n");
    print("<table>\n<tr>");
    print("<th>Picture</th><th>Needed</th><th>Have</th><th>Part Name</th> <th>Color</th> <th>Part ID</th></tr>\n");

    for($j = 0; $j < $index; $j++){
  
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
          $filename = "noimage_small.png";
        }
    
        $picSource = $prefix . $filename;

        if($quantity < $completeSetParts[$j]['Quantity']){
          continue;
        }
        else{
          print("<tr><td><img src='$picSource' alt='Img missing'></td><td>$piecesNeeded</td><td class='centerTd'>$quantity</td><td>$partName</td> <td class='centerTd'>$colorName</td> <td class='centerTd'>$partID</td></tr>");
        }
    }

    if(!empty($ownedMinifigsArray[0])){
      print("<tr><th colspan='5'>Minifigs:</th></tr>");
      print("<tr><th colspan='1'>Image</th><th colspan='1'>Quantity</th><th colspan='2'>Name</th><th colspan='1'>Minifig ID</th></tr>");

      for($j = 0; $j < $minifigIndex; $j++){
        $name = $ownedMinifigsArray[$j]['Name'];
        $minifigID = $ownedMinifigsArray[$j]['ItemID'];
        $quantity = $ownedMinifigsArray[$j]['Quantity'];
        
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
            $filename = "noimage_small.png";
        }
    
        $picSource = $prefix . $filename;

        print("<tr><td colspan='1'><img src='$picSource' alt='Img missing'><td colspan='1'>$quantity</td></td><td colspan='2' class='centerTd'>$name</td><td colspan='1'>$minifigID</td></tr>");
      }
    }
    
    print("</table>");
    print("</div>");

    //print missing pieces of this set from array
    print("<button class='accordion'>Pieces you miss for this set</button>");
    print("<div class='panel'>\n");
    print("<table>\n<tr>");
    print("<th>Picture</th><th>Needed</th><th>Have</th><th>Part Name</th> <th>Color</th> <th>Part ID</th></tr>\n");

    for($j = 0; $j < $index; $j++){
      
      $quantity = $ownedPartsArray[$j]['Quantity'];
      $partName = $ownedPartsArray[$j]['Partname'];
      $colorName = $ownedPartsArray[$j]['Colorname'];
      $partID = $ownedPartsArray[$j]['PartID'];
      $colorID = $ownedPartsArray[$j]['ColorID'];
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
        $filename = "noimage_small.png";
      }
  
      $picSource = $prefix . $filename;

      if($quantity >= $completeSetParts[$j]['Quantity']){
        continue;
      }
      else{
        //creates array for missing pieces before printning.
        $missingPartsArray[$j]['Quantity'] = $quantity;
        $missingPartsArray[$j]['PartID'] = $partID;
        $missingPartsArray[$j]['Partname'] = $partName;
        $missingPartsArray[$j]['ColorID'] = $colorID;
        $missingPartsArray[$j]['Colorname'] = $colorName;

        print("<tr><td><img src='$picSource' alt='Img missing'></td><td>$piecesNeeded</td><td class='centerTd'>$quantity</td><td>$partName</td> <td class='centerTd'>$colorName</td> <td class='centerTd'>$partID</td></tr>");
      }
  }

  if(!empty($missingMinifigs[0])){
    print("<tr><th colspan='5'>Minifigs:</th></tr>");
    print("<tr><th colspan='1'>Image</th><th colspan='1'>Quantity</th><th colspan='2'>Name</th><th colspan='1'>Minifig ID</th></tr>");

    for($j = 0; $j < $minifigIndex; $j++){
      $name = $missingMinifigs[$j]['Name'];
      $minifigID = $missingMinifigs[$j]['ItemID'];
      $quantity = $missingMinifigs[$j]['Quantity'];
      
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
          $filename = "noimage_small.png";
      }
  
      $picSource = $prefix . $filename;

      print("<tr><td colspan='1'><img src='$picSource' alt='Img missing'><td colspan='1'>$quantity</td></td><td colspan='2' class='centerTd'>$name</td><td colspan='1'>$minifigID</td></tr>");
    }
  }

  print("</table>");
  print("</div>");

    //stores different color of piece in an array
    for($j = 0; $j < $index; $j++){
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

    //searches for all pieces of same color and stores
    for($j = 0; $j < $index; $j++){
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



    print("<button class='accordion'>Pieces you own of this set but in the wrong color</button>");
    print("<div class='panel'>\n");
    print("<table>\n<tr>");
    print("<th>Picture</th><th>Needed</th><th>Have</th><th>Part Name</th> <th>Color</th> <th>Part ID</th></tr>\n");

    for($j = 0; $j < $index; $j++){
      if(array_key_exists($j, $differentColor)){
        $quantity = $differentColor[$j]['Quantity'];
        $partName = $differentColor[$j]['Partname'];
        $colorName = $differentColor[$j]['Colorname'];
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
          $filename = "noimage_small.png";
        }
  
        $picSource = $prefix . $filename;

        print("<tr><td><img src='$picSource' alt='Img missing'></td><td>$piecesNeeded</td><td class='centerTd'>$quantity</td><td>$partName</td> <td class='centerTd'>$colorName</td> <td class='centerTd'>$partID</td></tr>");
      }
    }

    print("</table>");
    print("</div>");
}

  else{

    $query = mysqli_query($connection, "SELECT sets.SetID, sets.Setname FROM sets, collection WHERE collection.SetID=sets.SetID LIMIT 20");



    while($row = mysqli_fetch_array($query)) {
      
      $prefix = "http://www.itn.liu.se/~stegu76/img.bricklink.com/";
      $SetID = $row['SetID'];
      $setName = $row['Setname'];
      
      $imagesearch = mysqli_query($connection, "SELECT * FROM images, sets WHERE ItemTypeID='S' AND SetID='$SetID' AND images.ItemID=sets.SetID");
      
      $imageinfo = mysqli_fetch_array($imagesearch);
      if($imageinfo['has_largejpg']) { 
        $filename = "SL/$SetID.jpg";
      } 
      else if($imageinfo['has_largegif']) { 
        $filename = "SL/$SetID.gif";
      } 
      else { 
        $filename = "noimage_small.png";
      }

      $picSource = $prefix . $filename;

      $query2 = mysqli_query($connection, "SELECT parts.Partname, inventory.Quantity FROM parts, inventory WHERE inventory.SetID='$SetID' AND inventory.ItemID=parts.PartID LIMIT 5");
      
      print('<div class="setBox">');
      print("<p>Set: $SetID</p> <p>$setName</p>");
      print("<img class='setPic' src=\"$picSource\" alt='Picture of Set' />");
      print('<ul class="setList">');
      while($row2 = mysqli_fetch_array($query2)) {
        $partName = $row2['Partname'];
        $quantity = $row2['Quantity'];
        print("<li>$quantity x $partName</li>");
      }
      print('</ul>');
      print("<a class='readMore' href='index.php?setID=$SetID'><p>Read More...</p></a>");
      print('</div>');
      
    }
  }
  ?>

</main>
<script src="../JS/accordion.js"></script>
</body>

</html>





<!-- <div>Font made from <a href="http://www.onlinewebfonts.com">oNline Web Fonts</a>is licensed by CC BY 3.0</div> -->