<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v16/errors/asset_group_listing_group_filter_error.proto

namespace Google\Ads\GoogleAds\V16\Errors\AssetGroupListingGroupFilterErrorEnum;

use UnexpectedValueException;

/**
 * Enum describing possible asset group listing group filter errors.
 *
 * Protobuf type <code>google.ads.googleads.v16.errors.AssetGroupListingGroupFilterErrorEnum.AssetGroupListingGroupFilterError</code>
 */
class AssetGroupListingGroupFilterError
{
    /**
     * Enum unspecified.
     *
     * Generated from protobuf enum <code>UNSPECIFIED = 0;</code>
     */
    const UNSPECIFIED = 0;
    /**
     * The received error code is not known in this version.
     *
     * Generated from protobuf enum <code>UNKNOWN = 1;</code>
     */
    const UNKNOWN = 1;
    /**
     * Listing group tree is too deep.
     *
     * Generated from protobuf enum <code>TREE_TOO_DEEP = 2;</code>
     */
    const TREE_TOO_DEEP = 2;
    /**
     * Listing Group UNIT node cannot have children.
     *
     * Generated from protobuf enum <code>UNIT_CANNOT_HAVE_CHILDREN = 3;</code>
     */
    const UNIT_CANNOT_HAVE_CHILDREN = 3;
    /**
     * Listing Group SUBDIVISION node must have everything else child.
     *
     * Generated from protobuf enum <code>SUBDIVISION_MUST_HAVE_EVERYTHING_ELSE_CHILD = 4;</code>
     */
    const SUBDIVISION_MUST_HAVE_EVERYTHING_ELSE_CHILD = 4;
    /**
     * Dimension type of Listing Group must be the same as that of its siblings.
     *
     * Generated from protobuf enum <code>DIFFERENT_DIMENSION_TYPE_BETWEEN_SIBLINGS = 5;</code>
     */
    const DIFFERENT_DIMENSION_TYPE_BETWEEN_SIBLINGS = 5;
    /**
     * The sibling Listing Groups target exactly the same dimension value.
     *
     * Generated from protobuf enum <code>SAME_DIMENSION_VALUE_BETWEEN_SIBLINGS = 6;</code>
     */
    const SAME_DIMENSION_VALUE_BETWEEN_SIBLINGS = 6;
    /**
     * The dimension type is the same as one of the ancestor Listing Groups.
     *
     * Generated from protobuf enum <code>SAME_DIMENSION_TYPE_BETWEEN_ANCESTORS = 7;</code>
     */
    const SAME_DIMENSION_TYPE_BETWEEN_ANCESTORS = 7;
    /**
     * Each Listing Group tree must have a single root.
     *
     * Generated from protobuf enum <code>MULTIPLE_ROOTS = 8;</code>
     */
    const MULTIPLE_ROOTS = 8;
    /**
     * Invalid Listing Group dimension value.
     *
     * Generated from protobuf enum <code>INVALID_DIMENSION_VALUE = 9;</code>
     */
    const INVALID_DIMENSION_VALUE = 9;
    /**
     * Hierarchical dimension must refine a dimension of the same type.
     *
     * Generated from protobuf enum <code>MUST_REFINE_HIERARCHICAL_PARENT_TYPE = 10;</code>
     */
    const MUST_REFINE_HIERARCHICAL_PARENT_TYPE = 10;
    /**
     * Invalid Product Bidding Category.
     *
     * Generated from protobuf enum <code>INVALID_PRODUCT_BIDDING_CATEGORY = 11;</code>
     */
    const INVALID_PRODUCT_BIDDING_CATEGORY = 11;
    /**
     * Modifying case value is allowed only while updating the entire subtree at
     * the same time.
     *
     * Generated from protobuf enum <code>CHANGING_CASE_VALUE_WITH_CHILDREN = 12;</code>
     */
    const CHANGING_CASE_VALUE_WITH_CHILDREN = 12;
    /**
     * Subdivision node has children which must be removed first.
     *
     * Generated from protobuf enum <code>SUBDIVISION_HAS_CHILDREN = 13;</code>
     */
    const SUBDIVISION_HAS_CHILDREN = 13;
    /**
     * Dimension can't subdivide everything-else node in its own hierarchy.
     *
     * Generated from protobuf enum <code>CANNOT_REFINE_HIERARCHICAL_EVERYTHING_ELSE = 14;</code>
     */
    const CANNOT_REFINE_HIERARCHICAL_EVERYTHING_ELSE = 14;
    /**
     * This dimension type is not allowed in this context.
     *
     * Generated from protobuf enum <code>DIMENSION_TYPE_NOT_ALLOWED = 15;</code>
     */
    const DIMENSION_TYPE_NOT_ALLOWED = 15;
    /**
     * All the webpage filters under an AssetGroup should be distinct.
     *
     * Generated from protobuf enum <code>DUPLICATE_WEBPAGE_FILTER_UNDER_ASSET_GROUP = 16;</code>
     */
    const DUPLICATE_WEBPAGE_FILTER_UNDER_ASSET_GROUP = 16;
    /**
     * Filter of the listing source type is not allowed in the context.
     *
     * Generated from protobuf enum <code>LISTING_SOURCE_NOT_ALLOWED = 17;</code>
     */
    const LISTING_SOURCE_NOT_ALLOWED = 17;
    /**
     * Exclusion filters are not allowed in the context.
     *
     * Generated from protobuf enum <code>FILTER_EXCLUSION_NOT_ALLOWED = 18;</code>
     */
    const FILTER_EXCLUSION_NOT_ALLOWED = 18;
    /**
     * All the filters under an AssetGroup should have the same listing source.
     *
     * Generated from protobuf enum <code>MULTIPLE_LISTING_SOURCES = 19;</code>
     */
    const MULTIPLE_LISTING_SOURCES = 19;
    /**
     * All the conditions in a webpage needs to be of same type.
     *
     * Generated from protobuf enum <code>MULTIPLE_WEBPAGE_CONDITION_TYPES_NOT_ALLOWED = 20;</code>
     */
    const MULTIPLE_WEBPAGE_CONDITION_TYPES_NOT_ALLOWED = 20;
    /**
     * All the webpage types of the filters under an AssetGroup should be of
     * same type. Example: All the webpage types can be of type custom_label or
     * url_contains but not both.
     *
     * Generated from protobuf enum <code>MULTIPLE_WEBPAGE_TYPES_PER_ASSET_GROUP = 21;</code>
     */
    const MULTIPLE_WEBPAGE_TYPES_PER_ASSET_GROUP = 21;
    /**
     * All page feed filter nodes are root nodes and they can't have a parent.
     *
     * Generated from protobuf enum <code>PAGE_FEED_FILTER_HAS_PARENT = 22;</code>
     */
    const PAGE_FEED_FILTER_HAS_PARENT = 22;
    /**
     * There cannot be more than one mutate operation per request that targets a
     * single asset group listing group filter.
     *
     * Generated from protobuf enum <code>MULTIPLE_OPERATIONS_ON_ONE_NODE = 23;</code>
     */
    const MULTIPLE_OPERATIONS_ON_ONE_NODE = 23;

    private static $valueToName = [
        self::UNSPECIFIED => 'UNSPECIFIED',
        self::UNKNOWN => 'UNKNOWN',
        self::TREE_TOO_DEEP => 'TREE_TOO_DEEP',
        self::UNIT_CANNOT_HAVE_CHILDREN => 'UNIT_CANNOT_HAVE_CHILDREN',
        self::SUBDIVISION_MUST_HAVE_EVERYTHING_ELSE_CHILD => 'SUBDIVISION_MUST_HAVE_EVERYTHING_ELSE_CHILD',
        self::DIFFERENT_DIMENSION_TYPE_BETWEEN_SIBLINGS => 'DIFFERENT_DIMENSION_TYPE_BETWEEN_SIBLINGS',
        self::SAME_DIMENSION_VALUE_BETWEEN_SIBLINGS => 'SAME_DIMENSION_VALUE_BETWEEN_SIBLINGS',
        self::SAME_DIMENSION_TYPE_BETWEEN_ANCESTORS => 'SAME_DIMENSION_TYPE_BETWEEN_ANCESTORS',
        self::MULTIPLE_ROOTS => 'MULTIPLE_ROOTS',
        self::INVALID_DIMENSION_VALUE => 'INVALID_DIMENSION_VALUE',
        self::MUST_REFINE_HIERARCHICAL_PARENT_TYPE => 'MUST_REFINE_HIERARCHICAL_PARENT_TYPE',
        self::INVALID_PRODUCT_BIDDING_CATEGORY => 'INVALID_PRODUCT_BIDDING_CATEGORY',
        self::CHANGING_CASE_VALUE_WITH_CHILDREN => 'CHANGING_CASE_VALUE_WITH_CHILDREN',
        self::SUBDIVISION_HAS_CHILDREN => 'SUBDIVISION_HAS_CHILDREN',
        self::CANNOT_REFINE_HIERARCHICAL_EVERYTHING_ELSE => 'CANNOT_REFINE_HIERARCHICAL_EVERYTHING_ELSE',
        self::DIMENSION_TYPE_NOT_ALLOWED => 'DIMENSION_TYPE_NOT_ALLOWED',
        self::DUPLICATE_WEBPAGE_FILTER_UNDER_ASSET_GROUP => 'DUPLICATE_WEBPAGE_FILTER_UNDER_ASSET_GROUP',
        self::LISTING_SOURCE_NOT_ALLOWED => 'LISTING_SOURCE_NOT_ALLOWED',
        self::FILTER_EXCLUSION_NOT_ALLOWED => 'FILTER_EXCLUSION_NOT_ALLOWED',
        self::MULTIPLE_LISTING_SOURCES => 'MULTIPLE_LISTING_SOURCES',
        self::MULTIPLE_WEBPAGE_CONDITION_TYPES_NOT_ALLOWED => 'MULTIPLE_WEBPAGE_CONDITION_TYPES_NOT_ALLOWED',
        self::MULTIPLE_WEBPAGE_TYPES_PER_ASSET_GROUP => 'MULTIPLE_WEBPAGE_TYPES_PER_ASSET_GROUP',
        self::PAGE_FEED_FILTER_HAS_PARENT => 'PAGE_FEED_FILTER_HAS_PARENT',
        self::MULTIPLE_OPERATIONS_ON_ONE_NODE => 'MULTIPLE_OPERATIONS_ON_ONE_NODE',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssetGroupListingGroupFilterError::class, \Google\Ads\GoogleAds\V16\Errors\AssetGroupListingGroupFilterErrorEnum_AssetGroupListingGroupFilterError::class);

