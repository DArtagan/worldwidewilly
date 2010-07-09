<?php
/**
 * Displays input elements for color settings form.
 */
?>
		<tr>
			<td></td>
			<td><input type="hidden" name="tgt" id="tgt" value="backgroundColor" /></td>
		</tr>		
		<tr>
			<td><label for="backgroundColor">controlbar</label></td>
			<td><input class="color" type="text" name="backgroundColor" id="backgroundColor" value="<?php echo $fp->conf['backgroundColor']; ?>" /></td>
		</tr>		
		<tr>
			<td><label for="canvas">canvas</label></td>
			<td><input class="color" type="text" name="canvas" id="canvas" value="<?php echo $fp->conf['canvas']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="sliderColor">sliders</label></td>
			<td><input class="color" type="text" name="sliderColor" id="sliderColor" value="<?php echo $fp->conf['sliderColor']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="buttonColor">buttons</label></td>
			<td><input class="color" type="text" name="buttonColor" id="buttonColor" value="<?php echo $fp->conf['buttonColor']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="buttonOverColor">mouseover</label></td>
			<td><input class="color" type="text" name="buttonOverColor" id="buttonOverColor" value="<?php echo $fp->conf['buttonOverColor']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="durationColor">total time</label></td>
			<td><input class="color" type="text" name="durationColor" id="durationColor" value="<?php echo $fp->conf['durationColor']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="timeColor">time</label></td>
			<td><input class="color" type="text" name="timeColor" id="timeColor" value="<?php echo $fp->conf['timeColor']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="progressColor">progress</label></td>
			<td><input class="color" type="text" name="progressColor" id="progressColor" value="<?php echo $fp->conf['progressColor']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="bufferColor">buffer</label></td>
			<td><input class="color" type="text" name="bufferColor" id="bufferColor" value="<?php echo $fp->conf['bufferColor']; ?>" /></td>
		</tr>

