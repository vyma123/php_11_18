<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/translate/v3/automl_translation.proto

namespace Google\Cloud\Translate\V3;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A trained translation model.
 *
 * Generated from protobuf message <code>google.cloud.translation.v3.Model</code>
 */
class Model extends \Google\Protobuf\Internal\Message
{
    /**
     * The resource name of the model, in form of
     * `projects/{project-number-or-id}/locations/{location_id}/models/{model_id}`
     *
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * The name of the model to show in the interface. The name can be
     * up to 32 characters long and can consist only of ASCII Latin letters A-Z
     * and a-z, underscores (_), and ASCII digits 0-9.
     *
     * Generated from protobuf field <code>string display_name = 2;</code>
     */
    private $display_name = '';
    /**
     * The dataset from which the model is trained, in form of
     * `projects/{project-number-or-id}/locations/{location_id}/datasets/{dataset_id}`
     *
     * Generated from protobuf field <code>string dataset = 3;</code>
     */
    private $dataset = '';
    /**
     * Output only. The BCP-47 language code of the source language.
     *
     * Generated from protobuf field <code>string source_language_code = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $source_language_code = '';
    /**
     * Output only. The BCP-47 language code of the target language.
     *
     * Generated from protobuf field <code>string target_language_code = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $target_language_code = '';
    /**
     * Output only. Number of examples (sentence pairs) used to train the model.
     *
     * Generated from protobuf field <code>int32 train_example_count = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $train_example_count = 0;
    /**
     * Output only. Number of examples (sentence pairs) used to validate the
     * model.
     *
     * Generated from protobuf field <code>int32 validate_example_count = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $validate_example_count = 0;
    /**
     * Output only. Number of examples (sentence pairs) used to test the model.
     *
     * Generated from protobuf field <code>int32 test_example_count = 12 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $test_example_count = 0;
    /**
     * Output only. Timestamp when the model resource was created, which is also
     * when the training started.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $create_time = null;
    /**
     * Output only. Timestamp when this model was last updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $update_time = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           The resource name of the model, in form of
     *           `projects/{project-number-or-id}/locations/{location_id}/models/{model_id}`
     *     @type string $display_name
     *           The name of the model to show in the interface. The name can be
     *           up to 32 characters long and can consist only of ASCII Latin letters A-Z
     *           and a-z, underscores (_), and ASCII digits 0-9.
     *     @type string $dataset
     *           The dataset from which the model is trained, in form of
     *           `projects/{project-number-or-id}/locations/{location_id}/datasets/{dataset_id}`
     *     @type string $source_language_code
     *           Output only. The BCP-47 language code of the source language.
     *     @type string $target_language_code
     *           Output only. The BCP-47 language code of the target language.
     *     @type int $train_example_count
     *           Output only. Number of examples (sentence pairs) used to train the model.
     *     @type int $validate_example_count
     *           Output only. Number of examples (sentence pairs) used to validate the
     *           model.
     *     @type int $test_example_count
     *           Output only. Number of examples (sentence pairs) used to test the model.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. Timestamp when the model resource was created, which is also
     *           when the training started.
     *     @type \Google\Protobuf\Timestamp $update_time
     *           Output only. Timestamp when this model was last updated.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Translate\V3\AutomlTranslation::initOnce();
        parent::__construct($data);
    }

    /**
     * The resource name of the model, in form of
     * `projects/{project-number-or-id}/locations/{location_id}/models/{model_id}`
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The resource name of the model, in form of
     * `projects/{project-number-or-id}/locations/{location_id}/models/{model_id}`
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * The name of the model to show in the interface. The name can be
     * up to 32 characters long and can consist only of ASCII Latin letters A-Z
     * and a-z, underscores (_), and ASCII digits 0-9.
     *
     * Generated from protobuf field <code>string display_name = 2;</code>
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * The name of the model to show in the interface. The name can be
     * up to 32 characters long and can consist only of ASCII Latin letters A-Z
     * and a-z, underscores (_), and ASCII digits 0-9.
     *
     * Generated from protobuf field <code>string display_name = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setDisplayName($var)
    {
        GPBUtil::checkString($var, True);
        $this->display_name = $var;

        return $this;
    }

    /**
     * The dataset from which the model is trained, in form of
     * `projects/{project-number-or-id}/locations/{location_id}/datasets/{dataset_id}`
     *
     * Generated from protobuf field <code>string dataset = 3;</code>
     * @return string
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * The dataset from which the model is trained, in form of
     * `projects/{project-number-or-id}/locations/{location_id}/datasets/{dataset_id}`
     *
     * Generated from protobuf field <code>string dataset = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setDataset($var)
    {
        GPBUtil::checkString($var, True);
        $this->dataset = $var;

        return $this;
    }

    /**
     * Output only. The BCP-47 language code of the source language.
     *
     * Generated from protobuf field <code>string source_language_code = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getSourceLanguageCode()
    {
        return $this->source_language_code;
    }

    /**
     * Output only. The BCP-47 language code of the source language.
     *
     * Generated from protobuf field <code>string source_language_code = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setSourceLanguageCode($var)
    {
        GPBUtil::checkString($var, True);
        $this->source_language_code = $var;

        return $this;
    }

    /**
     * Output only. The BCP-47 language code of the target language.
     *
     * Generated from protobuf field <code>string target_language_code = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getTargetLanguageCode()
    {
        return $this->target_language_code;
    }

    /**
     * Output only. The BCP-47 language code of the target language.
     *
     * Generated from protobuf field <code>string target_language_code = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setTargetLanguageCode($var)
    {
        GPBUtil::checkString($var, True);
        $this->target_language_code = $var;

        return $this;
    }

    /**
     * Output only. Number of examples (sentence pairs) used to train the model.
     *
     * Generated from protobuf field <code>int32 train_example_count = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return int
     */
    public function getTrainExampleCount()
    {
        return $this->train_example_count;
    }

    /**
     * Output only. Number of examples (sentence pairs) used to train the model.
     *
     * Generated from protobuf field <code>int32 train_example_count = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param int $var
     * @return $this
     */
    public function setTrainExampleCount($var)
    {
        GPBUtil::checkInt32($var);
        $this->train_example_count = $var;

        return $this;
    }

    /**
     * Output only. Number of examples (sentence pairs) used to validate the
     * model.
     *
     * Generated from protobuf field <code>int32 validate_example_count = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return int
     */
    public function getValidateExampleCount()
    {
        return $this->validate_example_count;
    }

    /**
     * Output only. Number of examples (sentence pairs) used to validate the
     * model.
     *
     * Generated from protobuf field <code>int32 validate_example_count = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param int $var
     * @return $this
     */
    public function setValidateExampleCount($var)
    {
        GPBUtil::checkInt32($var);
        $this->validate_example_count = $var;

        return $this;
    }

    /**
     * Output only. Number of examples (sentence pairs) used to test the model.
     *
     * Generated from protobuf field <code>int32 test_example_count = 12 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return int
     */
    public function getTestExampleCount()
    {
        return $this->test_example_count;
    }

    /**
     * Output only. Number of examples (sentence pairs) used to test the model.
     *
     * Generated from protobuf field <code>int32 test_example_count = 12 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param int $var
     * @return $this
     */
    public function setTestExampleCount($var)
    {
        GPBUtil::checkInt32($var);
        $this->test_example_count = $var;

        return $this;
    }

    /**
     * Output only. Timestamp when the model resource was created, which is also
     * when the training started.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    public function hasCreateTime()
    {
        return isset($this->create_time);
    }

    public function clearCreateTime()
    {
        unset($this->create_time);
    }

    /**
     * Output only. Timestamp when the model resource was created, which is also
     * when the training started.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->create_time = $var;

        return $this;
    }

    /**
     * Output only. Timestamp when this model was last updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    public function hasUpdateTime()
    {
        return isset($this->update_time);
    }

    public function clearUpdateTime()
    {
        unset($this->update_time);
    }

    /**
     * Output only. Timestamp when this model was last updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setUpdateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->update_time = $var;

        return $this;
    }

}
