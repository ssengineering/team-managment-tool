<?php

echo "<div id = 'EditTicketTable' style='width:97%; display:none;'> <h3>Edit Ticket Review:  </h3>

			<!--Form to submit the ticket review to the database.-->
			<FORM METHOD='POST' NAME='ticketReviewForm' onsubmit='return validateForm()'>
			 <TABLE BORDER='1'  id='editTicketTableEntry'>
			    <TR>
				    <TH>Ticket#</TH><TH>Ticket Date</TH><TH>Requestor</TH><TH>Contact Info</TH><TH>Service Category</TH>
				    <TH>Ticket <br />Source</TH><TH>Priority</TH><TH>KB/Source</TH>
			  	</TR> 
				
				<TR >
					<TD><INPUT TYPE='TEXT' NAME='editTicketNum' id='editTicketNum' maxlength='10' SIZE='10' PLACEHOLDER='INC0034567'></TD>
			    	<TD><INPUT TYPE='TEXT' NAME='editTicketDate' id='editTicketDate' class='tcal' SIZE='8' PLACEHOLDER='YYYY-MM-DD' value='' ></TD>					
					<TD class='cellCenter'>
						<select name='editRequestor' id='editRequestor'>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
						</select>
					</TD>
					<TD class='cellCenter'>
						<select name='editContactInfo' id='editContactInfo'>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
						</select>
					</TD>
					<TD class='cellCenter'>
						<select name='editServiceOrSymtomCat' id='editServiceOrSymtomCat' >
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
					    </select>	
					</TD>
					<TD class='cellCenter'>
						<select name='editTicketSource' id='editTicketSource'>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
						</select>
					</TD>
					 <TD class='cellCenter'>
						<select name'editPriority' id='editPriority'>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
						</select>
					</TD>
					<TD class='cellCenter'>
						<select name='editKBOrSource' id='editKBOrSource'>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
						</select>
					</TD>					
					
				</TR>
				<TR>
					<TH class='deliniateLeft'>Work Order#</TH><TH>Template</TH><TH>Trouble- shooting</TH><TH>Closure Codes</TH><TH>Professio- nalism</TH><TH colspan='3'>Comments</TH>
				</TR>
				<TR>
					<TD class='cellCenter'>
						<select name='editWorkOrder' id='editWorkOrder'>
						<option selected value='NA'>NA</option>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
					  	</select>
					</TD>
					<TD class='cellCenter'>
						<select name='editTemplate' id='editTemplate'>
						<option selected value='NA'>NA</option>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
					  	</select>
					</TD>
					<TD class='cellCenter'>
						<select name='editTroubleshooting' id='editTroubleshooting'>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
					  	</select>
					</TD>
					<TD class='cellCenter'>
						<select name='editClosureCodes' id='editClosureCodes'>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
					  	</select>
					</TD>
					<TD class='cellCenter'>
						<select name='editProfessionalism' id='editProfessionalism'>
						<option value='Yes'>Yes</option>
						<option value='No'>No</option>
					  	</select>
					   </TD>
				   <TD colspan='3' ><textarea rows='1' cols='45' name='editComment' id='editComment' style='resize:both;  max-height:300px; min-height:30px;  max-width:343px; min-width:343px;'></textarea></TD>
				</TR> 			 
			    </TABLE> 
				<INPUT TYPE='HIDDEN' VALUE='' name='editTicketNumEntry' id='editTicketNumEntry' />		
			</FORM>
			</div>";

?>
