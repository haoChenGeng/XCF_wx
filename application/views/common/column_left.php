<nav id="column-left"><div id="profile">
  	<div>
        <i class="fa fa-shopping-cart"></i>
    </div>
	<div>
    	<h4><?php echo $_SESSION['fullname'];?></h4>
    	<small><?php echo $_SESSION['groupName'];?></small>
  		</div>
	</div>
	<ul id="menu">
    	<?php if(isset($menu)){ 
    		foreach ($menu as $k1 => $v1){
    			echo '<li id="'.$v1['name'].'"><a class="parent"><i class="fa fa-tags fa-fw"></i> <span>'.$v1['description'].'</span></a>';
    			if (isset($v1['menu'])){
    				foreach ($v1['menu'] as $k2=>$v2){
    					echo "<ul>";
    					if (!empty($v2['command'])) {
    						echo '<li><a href="'.$this->unifyEntrance.$menuCode[$v2['val']].'">'.$v2['description']."</a></li>";    //$v2['command']
    					}else{
    						echo '<li><a class="parent">'.$v2['description'].'</a>';
    					}
    					if (isset($v2['menu'])){
    						foreach ($v2['menu'] as $k3=>$v3){
    							$url = isset($menuCode[$v3["val"]]) ? $this->unifyEntrance.$menuCode[$v3['val']] : '#';
    							echo "<ul>";
    							echo '<li><a href="'.$url.'">'.$v3['description']."</a></li>";
    							echo "</ul>";
    						}
    					}
    					echo "</ul>";
    				}
    			}
    		}
    	}?>
	</ul>
</nav>
