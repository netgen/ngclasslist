<?php

/**
 * NgClassListType class implements the ngclasslist datatype
 */
class NgClassListType extends eZDataType
{
    const DATA_TYPE_STRING = "ngclasslist";

    const CLASS_LIST_VARIABLE = "_ngclasslist_class_list_";
    const CLASS_LIST_FIELD = "data_text";

    const DEFAULT_VALUE_VARIABLE = '_ngclasslist_class_list_default_value_';
    const ALLOWED_GROUP_VARIABLE = '_ngclasslist_allowed_groups_';
    const CLASS_ATTRIBUTE_LIST_FIELD = "data_text5";

    private static $classesByGroup;

    /**
     * @var eZContentClass[]
     */
    private static $allClasses;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::eZDataType(
            self::DATA_TYPE_STRING,
            ezpI18n::tr( "extension/ngclasslist/datatypes", "Class list" ),
            array( "serialize_supported" => true )
        );

        if (self::$allClasses === null){
            self::$allClasses = eZContentClass::fetchAllClasses();
        }

        if (self::$classesByGroup === null){
            foreach(self::$allClasses as $class){
                /** @var eZContentClassClassGroup[] $groupList */
                $groupList = $class->fetchGroupList();
                foreach($groupList as $group){
                    if (!isset(self::$classesByGroup[$group->attribute('group_name')])){
                        self::$classesByGroup[$group->attribute('group_name')] = array();
                    }
                    self::$classesByGroup[$group->attribute('group_name')][] = $class;
                }
            }
        }
    }

    /**
     * Sets the default value
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObjectVersion $currentVersion
     * @param eZContentObjectAttribute $originalContentObjectAttribute
     */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $data = trim( $originalContentObjectAttribute->attribute( self::CLASS_LIST_FIELD ) );
            $contentObjectAttribute->setAttribute( self::CLASS_LIST_FIELD, $data );
        }else{
            $classAttributeContent = $contentObjectAttribute->contentClassAttribute()->attribute('content');
            $contentObjectAttribute->setAttribute( self::CLASS_LIST_FIELD, implode(',', (array)$classAttributeContent['selected_class_identifiers']) );
        }
    }

    /**
     * Validates the input and returns the validity status code
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return int
     */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $classList = $http->postVariable( $base . self::CLASS_LIST_VARIABLE . $contentObjectAttribute->attribute( "id" ), array() );
        $classList = !is_array( $classList ) ? array() : $classList;

        if ( empty( $classList ) && $contentObjectAttribute->validateIsRequired() )
        {
            $contentObjectAttribute->setValidationError( ezpI18n::tr( "kernel/classes/datatypes", "Input required." ) );
            return eZInputValidator::STATE_INVALID;
        }

        $invalidClassIdentifiers = array();

        foreach ( $classList as $classIdentifier )
        {
            if ( !eZContentClass::exists( $classIdentifier, eZContentClass::VERSION_STATUS_DEFINED, false, true ) )
            {
                $invalidClassIdentifiers[] = $classIdentifier;
            }
        }

        if ( !empty( $invalidClassIdentifiers ) )
        {
            if ( count( $invalidClassIdentifiers ) == 1 )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( "extension/ngclasslist/datatypes", "Class with identifier '%identifier%' does not exist", null, array( "%identifier%" => $invalidClassIdentifiers[0] ) ) );
            }
            else
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( "extension/ngclasslist/datatypes", "Classes with '%identifiers%' identifiers do not exist", null, array( "%identifiers%" => implode( ", ", $invalidClassIdentifiers ) ) ) );
            }

            return eZInputValidator::STATE_INVALID;
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * Fetches the HTTP POST input and stores it in the data instance
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return bool
     */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $classList = $http->postVariable( $base . self::CLASS_LIST_VARIABLE . $contentObjectAttribute->attribute( "id" ), array() );
        $classList = !is_array( $classList ) ? array() : $classList;

        $validClassIdentifiers = array();

        foreach ( $classList as $classIdentifier )
        {
            if ( eZContentClass::exists( $classIdentifier, eZContentClass::VERSION_STATUS_DEFINED, false, true ) )
            {
                $validClassIdentifiers[] = $classIdentifier;
            }
        }

        if ( !empty( $validClassIdentifiers ) )
        {
            $contentObjectAttribute->setAttribute( self::CLASS_LIST_FIELD, implode( ",", $validClassIdentifiers ) );
        }
        else
        {
            $contentObjectAttribute->setAttribute( self::CLASS_LIST_FIELD, "" );
        }

        return true;
    }

    /**
     * Does nothing since it uses the data_text field in the content object attribute.
     * See fetchObjectAttributeHTTPInput for the actual storing.
     *
     * @param eZContentObjectAttribute $attribute
     */
    function storeObjectAttribute( $attribute )
    {
    }

    /**
     * Returns the content.
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return string
     */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $dataText = $contentObjectAttribute->attribute( self::CLASS_LIST_FIELD );

        $content = array(
            "classes" => array(),
            "class_identifiers" => array(),
            "class_ids" => array()
        );

        if ( !empty( $dataText ) )
        {
            $classIdentifiers = explode( ",", $dataText );

            foreach ( $classIdentifiers as $classIdentifier )
            {
                $class = eZContentClass::fetchByIdentifier( $classIdentifier );
                if ( $class instanceof eZContentClass )
                {
                    $content["classes"][] = $class;
                    $content["class_identifiers"][] = $classIdentifier;
                    $content["class_ids"][] = (int) $class->attribute( "id" );
                }
            }
        }

        return $content;
    }

    /**
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentClassAttribute $classAttribute
     */
    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $defaultValueName = $base . self::DEFAULT_VALUE_VARIABLE . $classAttribute->attribute( 'id' );
        $allowedGroupsName = $base . self::ALLOWED_GROUP_VARIABLE . $classAttribute->attribute( 'id' );

        $data = array(
            'default_class_identifiers' => false,
            'allowed_groups' => false
        );
        if ( $http->hasPostVariable( $defaultValueName ) )
        {
            $defaultValue = $http->postVariable( $defaultValueName );
            $data['default_class_identifiers'] = array_unique($defaultValue);
        }
        if ( $http->hasPostVariable( $allowedGroupsName ) )
        {
            $allowedGroups = $http->postVariable( $allowedGroupsName );
            $data['allowed_groups'] = $allowedGroups;
        }

        $classAttribute->setAttribute(self::CLASS_ATTRIBUTE_LIST_FIELD, json_encode($data));
        $classAttribute->store();
    }

    /**
     * @param eZContentClassAttribute $classAttribute
     *
     * @return array
     */
    function classAttributeContent( $classAttribute )
    {
        $content = array(
            "selected_class_identifiers" => array(),
            "allowed_groups" => array(),
            "classes_by_group" => self::$classesByGroup
        );

        $data = json_decode($classAttribute->attribute(self::CLASS_ATTRIBUTE_LIST_FIELD), true);

        if ( !empty( $data['default_class_identifiers'] ) )
        {
            $content['selected_class_identifiers'] = $data['default_class_identifiers'];
        }

        if ( !empty( $data['allowed_groups'] ) )
        {
            $content["allowed_groups"] = $data['allowed_groups'];
        }

        return $content;
    }

    /**
     * Returns string representation of data for simplified export
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return string
     */
    function toString( $contentObjectAttribute )
    {
        return trim( $contentObjectAttribute->attribute( self::CLASS_LIST_FIELD ) );
    }

    /**
     * Imports the data to the attribute
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string $string
     *
     * @return string
     */
    function fromString( $contentObjectAttribute, $string )
    {
        $contentObjectAttribute->setAttribute( self::CLASS_LIST_FIELD, trim( $string ) );
    }

    /**
     * Returns the content of the attribute for use as a title
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string $name
     *
     * @return string
     */
    function title( $contentObjectAttribute, $name = null )
    {
        return $contentObjectAttribute->attribute( self::CLASS_LIST_FIELD );
    }

    /**
     * Returns true if attribute has content, false otherwise
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return bool
     */
    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        $data = $this->objectAttributeContent( $contentObjectAttribute );
        return !empty( $data["class_identifiers"] );
    }

    /**
     * Returns if the content supports batch initialization
     *
     * @return bool
     */
    function supportsBatchInitializeObjectAttribute()
    {
        return true;
    }

    /**
     * @param eZContentClassAttribute $classAttribute
     *
     * @return array
     */
    function batchInitializeObjectAttributeData( $classAttribute )
    {
        $default = null;
        $classAttributeContent = $classAttribute->attribute('content');
        if (!empty($classAttributeContent['selected_class_identifiers']))
        {
            $db = eZDB::instance();
            $default = implode(',', (array)$classAttributeContent['selected_class_identifiers']);
            $default = "'" . $db->escapeString( $default ) . "'";
        }

        if ( $default !== '' && $default !== NULL )
        {
            return array( self::CLASS_LIST_FIELD => $default );
        }

        return array();
    }
}

eZDataType::register( NgClassListType::DATA_TYPE_STRING, "NgClassListType" );
