<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:d="http://doctrine-project.org/schemas/orm/doctrine-mapping"
    xmlns:php="http://php.net/xsl"
    >
    <xsl:param name="targetNs"></xsl:param>
    
	<xsl:template match="/">
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="text()">
    </xsl:template>
	
	
	
	<xsl:template match="root">
        <xsd:schema targetNamespace="{$targetNs}" elementFormDefault="qualified">
             <xsl:apply-templates select="d:*"/> 
                      

            <xsl:for-each select="//d:doctrine-mapping/d:entity/d:one-to-many|//d:doctrine-mapping/d:entity/d:many-to-many">
                <xsl:if test="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getTargetNsForType', string(@target-entity)) = string($targetNs)">
                    <xsl:apply-templates select="." mode="array"/>
                </xsl:if>
            </xsl:for-each>
            
            <xsd:complexType name="ArrayOfInt">
                <xsd:sequence>
                    <xsd:element name="int" type="xsd:integer" maxOccurs="unbounded" minOccurs="0"/>
                </xsd:sequence>
            </xsd:complexType>
            <xsd:complexType name="ArrayOfString">
                <xsd:sequence>
                    <xsd:element name="string" type="xsd:string" maxOccurs="unbounded" minOccurs="0"/>
                </xsd:sequence>
            </xsd:complexType>
        </xsd:schema>
    </xsl:template>
    
    <xsl:template match="d:doctrine-mapping">
            
       <xsl:for-each select="d:entity">            
             <xsl:if test="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getTargetNsForType', string(@name)) = string($targetNs)">
                 <xsl:apply-templates select="."/>
             </xsl:if>
         </xsl:for-each>
    </xsl:template>
    
    <xsl:template match="d:entity">
        <xsd:complexType>
            <xsl:attribute name="name">
                <xsl:value-of select="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getTypeName', string(@name), string($targetNs))"></xsl:value-of>
            </xsl:attribute>
            
            

	        <xsd:sequence>
	            <xsl:apply-templates select="d:field"/>
	            
	            <xsl:apply-templates select="d:one-to-one"/>
	            <xsl:apply-templates select="d:many-to-one"/>
	            <xsl:apply-templates select="d:one-to-many"/>
	            <xsl:apply-templates select="d:many-to-many"/>
	        </xsd:sequence>

	        <xsl:apply-templates select="d:id"/>
        </xsd:complexType>
    </xsl:template>
    
    <xsl:template match="d:one-to-one|d:many-to-one">
        <xsl:variable name="field" select="@field"/>
        <xsl:variable name="target" select="@target-entity"/>
        
        <xsl:if test="not(../d:id[@name=$field])">
	        <xsl:comment><xsl:value-of select="local-name()"/></xsl:comment>
	        <xsd:element name="{$field}" minOccurs="0" maxOccurs="1">
	            <xsl:attribute name="type">
	               <xsl:value-of select="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getXsdType', string(//d:doctrine-mapping/d:entity[@name=$target]/d:id/@type))"></xsl:value-of>
	            </xsl:attribute>
	        </xsd:element>
        </xsl:if>
     </xsl:template>
     
     
     <xsl:template match="d:one-to-many|d:many-to-many">
        <xsl:variable name="field" select="@field"/>
        <xsl:variable name="target" select="@target-entity"/>
        <xsl:if test="not(../d:id[@name=$field])">
            <xsl:comment><xsl:value-of select="local-name()"/></xsl:comment>
            <xsd:element name="{$field}" minOccurs="0" maxOccurs="1">
                <xsl:attribute name="type">                    
                    <xsl:variable name="targetEntity" select="//d:doctrine-mapping/d:entity[@name=$target]"/>  
                    
                    <xsl:choose>          
	                    <xsl:when test="$targetEntity/d:id[not(@association-key)]">
	                        <xsl:value-of select="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getXsdArrayType', string($targetEntity/d:id[not(@association-key)]/@type))"></xsl:value-of>
	                    </xsl:when>
	                    
	                    <xsl:otherwise>
	                       <xsl:text>ArrayOf</xsl:text>
	                       <xsl:value-of select="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getTypeName', string(@target-entity), string($targetNs))"></xsl:value-of>
	                    </xsl:otherwise>
                    </xsl:choose>
                    
                </xsl:attribute>
            </xsd:element>
        </xsl:if>
     </xsl:template>
     
    <xsl:template match="d:one-to-many|d:many-to-many" mode="array">
        <xsd:complexType>
            <xsl:attribute name="name">
                <xsl:text>ArrayOf</xsl:text>
	            <xsl:value-of select="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getTypeName', string(@target-entity), string($targetNs))"></xsl:value-of>
            </xsl:attribute>
            <xsd:sequence>
	            <xsd:element name="{php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getTypeName', string(@target-entity), string($targetNs))}" minOccurs="0" maxOccurs="unbounded">
	                <xsl:attribute name="type">
	                   <xsl:value-of select="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getTypeName', string(@target-entity), string($targetNs))"></xsl:value-of>
	                </xsl:attribute>
	            </xsd:element>
            </xsd:sequence>
        </xsd:complexType>
     </xsl:template>
     
     
     <xsl:template match="d:id">
        <xsd:attribute name="{@name}" use="required">
            <xsl:attribute name="type">
	           <xsl:value-of select="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getXsdType', string(@type))"></xsl:value-of>
            </xsl:attribute>
        </xsd:attribute>
     </xsl:template>
     <xsl:template match="d:field">
        <xsd:element name="{@name}" maxOccurs="1">
            <xsl:attribute name="minOccurs">
               <xsl:choose>
                    <xsl:when test="@nullable='false'">1</xsl:when>
                    <xsl:otherwise>0</xsl:otherwise>
               </xsl:choose>
            </xsl:attribute>
        
        
            <xsl:attribute name="type">
               <xsl:value-of select="php:function('Goetas\DoctrineToXsd\Mapper\TypeMapper::getXsdType', string(@type))"></xsl:value-of>
            </xsl:attribute>
        </xsd:element>
     </xsl:template>
</xsl:stylesheet>