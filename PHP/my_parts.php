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

  if(isset($_GET['partID']) &&  isset($_GET['colorID']) && isset($_GET['setID']) && isset($_GET['setName'])){
    $partID = $_GET['partID'];
    $colorID = $_GET['colorID'];
    $SetID = $_GET['setID'];
    $setName = $_GET['setName'];
    $partNeeded = 0;

    $query = mysqli_query($connection, "SELECT colors.ColorRGB, colors.Colorname FROM colors WHERE colors.ColorID='$colorID'");

    while($row = mysqli_fetch_array($query)) {
  
      $colorRGB = $row['ColorRGB'];
      $colorName = $row['Colorname'];

    } 
    $query2 = mysqli_query($connection, "SELECT inventory.Quantity FROM inventory WHERE inventory.SetID='$SetID' AND inventory.Extra='N' AND inventory.ItemID='$partID'");
    while($row2 = mysqli_fetch_array($query2)) {
  
      $partNeeded += $row2['Quantity'];
      }

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
    
    print('<div class="setBox">');
    print("<p>Set: $SetID</p> <p>$setName</p>");
    print("<img class='setPic' src=\"$picSource\" alt='Picture of Set' />");
    print('<ul class="setList">');

    //print("<div class ='colorBox' style='background-color: #$colorRGB'><p class='colorDescr'>$colorName</p></div>");

 

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

    


    $query = mysqli_query($connection, "SELECT DISTINCT inventory.ItemID, inventory.ColorID, parts.Partname, collection.Quantity setQuantity, colors.Colorname, colors.ColorRGB, inventory.Quantity 
  FROM inventory, parts, colors, collection WHERE inventory.ItemID=parts.PartID AND
  inventory.ColorID=colors.ColorID AND inventory.ItemID=parts.PartID AND parts.PartID='$partID' AND collection.SetID=inventory.SetID");

  while($row = mysqli_fetch_array($query)) {
    
    $prefix = "http://www.itn.liu.se/~stegu76/img.bricklink.com/";
    $partID = $row['ItemID'];
    $partName = $row['Partname'];
    //$colorID = $row['ColorID'];
    //$colorRGB = $row['ColorRGB'];
    //$colorName = $row['Colorname'];
  
    
    print('<div class="setBox">');
    print("<div class='currentItem'><p>Currently looking at: $partName</p><p>PartID: $partID</p></div>");
    print("<div class='currentPartsinfo'>");
    print("<p>Parts needed for this set: $partNeeded");
    print("<img class='setPic' src=\"$picSource\" alt='Picture of Set'/>");
    print("</div>");
   
    
    //print("<div class='colorBoxchosen' style='background-color: #$colorRGB'></div>");
    print("<br>");
    

    $index = 0 ;
    mysqli_data_seek($query, 0);
    while($row = mysqli_fetch_array($query)) {
      $colorSet[$index]['ColorID'] = $row['ColorID'];
      $colorSet[$index]['Quantity'] = $row['Quantity'];
      $colorSet[$index]['ColorRGB'] = $row['ColorRGB'];
      $colorSet[$index]['Colorname'] = $row['Colorname'];
      $colorSet[$index]['setQuantity'] = $row['setQuantity'];
      $index++;
      
    } 
    
    $totalQuantity = 0;
    for($i = 0; $i < $index; $i++){
      if($colorSet[$i]['ColorID'] == $colorID){
        $totalQuantity += $colorSet[$i]['Quantity'] * $colorSet[$i]['setQuantity'];
        
      }
    }
    print("<div class'currentIteminfo2>");
    print("<div class ='colorBoxchosen' style='background-color: #$colorRGB'><p class='colorDescr'>$colorName</p></div>");
    print("Total quantity of this color: $totalQuantity");
    print("</div>");

    print("</div>");
    
    $query2 = mysqli_query($connection, "SELECT DISTINCT colors.Colorname, colors.ColorRGB, colors.ColorID FROM inventory, parts, colors, collection WHERE inventory.ItemID=parts.PartID AND
    inventory.ColorID=colors.ColorID AND inventory.ItemID=parts.PartID AND parts.PartID='$partID' AND collection.SetID=inventory.SetID");

    print("<div id='colorBoxes'>");
    while($row = mysqli_fetch_array($query2)) {
      $colorRGB = $row['ColorRGB'];
      $colorName = $row['Colorname'];
      $colorID = $row['ColorID'];
      
      /*$totalQuantity = 0;
      for($i = 0; $i < $index; $i++){
        if($colorSet[$i]['ColorID'] == $colorID){
          $totalQuantity += $colorSet[$i]['Quantity'] * $colorSet[$i]['setQuantity'];
          
        }
      }*/
      

$colorRGB = str_replace('\'',' ', $colorRGB);


      print('<div class ="colorBox" onclick="changeColor(\''.$colorID.'\', \''.$SetID.'\', \''.$setName.'\', \''.$partID.'\')" style="background-color: #'.$colorRGB.'"><p class="colorDescr">\''.$colorName.'\'</p></div>');
    }  
    print("</div>");
  }
}
  /*else{

    $query = mysqli_query($connection, "SELECT DISTINCT categories.Categoryname, categories.CatID FROM collection, categories, sets WHERE collection.SetID=sets.SetID AND sets.CatID=categories.CatID");

    while($row = mysqli_fetch_array($query)) {
      $catName = $row['Categoryname'];
      $catID = $row['CatID'];
      print("<a href='http://www.student.itn.liu.se/~joner280/TNMK30/PROJEKTMASTER/PHP/my_parts.php?partID=$partID?setID=$setID'><p>$catName x $catID</p></a>");
    }

    */
  ?>
  </main>

</body>

</html>


