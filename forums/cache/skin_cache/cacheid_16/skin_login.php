<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 16               */
/* CACHE FILE: Generated: Thu, 13 Dec 2012 15:52:42 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_login_16 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();


}

/* -- ajax__inlineLogInForm --*/
function ajax__inlineLogInForm() {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- errors --*/
function errors($data="") {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- showLogInForm --*/
function showLogInForm($message="",$referer="",$extra_form="", $login_methods=array()) {
$IPBHTML = "";
$IPBHTML .= "<template>LoginRequired</template>
<categories>
	<category>
		<id>180</id>
		<name>
		<![CDATA[ Registration Required ]]>
		</name>
		<forums>
			<forum>
				<id>1</id>
				<name>
					<![CDATA[ Please login or register ]]>
				</name>
				<url>
					<![CDATA[]]>
				</url>
				<description>
					<![CDATA[]]>
				</description>
				<isRead>1</isRead>
				<redirect>0</redirect>
				<type/>
				<topics>0</topics>
				<replies>0</replies>
				<lastpost>
					<date>14 May 2012</date>
					<name>
						<![CDATA[]]>
					</name>
					<id>362680</id>
					<url>
						<![CDATA[]]>
					</url>
					<user>
						<id>0</id>
						<name>
						<![CDATA[]]>
						</name>
						<url>
							<![CDATA[]]>
						</url>
					</user>
				</lastpost>
			</forum>
		</forums>
	</category>
</categories>";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>