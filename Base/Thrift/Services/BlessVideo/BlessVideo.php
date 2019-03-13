<?php
namespace Services\BlessVideo;
/**
 * Autogenerated by Thrift Compiler (0.11.0)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;


interface BlessVideoIf {
  /**
   * @param string $title
   * @param int $status
   * @param array $selectLimit
   * @return \Services\BlessVideo\ListResult
   */
  public function getBlessVideoList($title, $status, array $selectLimit);
  /**
   * @param int $videoId
   * @return \Services\BlessVideo\ListResult
   */
  public function getBlessVideoDetail($videoId);
  /**
   * @param array $data
   * @return bool
   */
  public function saveBlessVideoRecord(array $data);
  /**
   * @param array $params
   * @return array
   */
  public function addPrizeQualification(array $params);
}


class BlessVideoClient implements \Services\BlessVideo\BlessVideoIf {
  protected $input_ = null;
  protected $output_ = null;

  protected $seqid_ = 0;

  public function __construct($input, $output=null) {
    $this->input_ = $input;
    $this->output_ = $output ? $output : $input;
  }

  public function getBlessVideoList($title, $status, array $selectLimit)
  {
    $this->send_getBlessVideoList($title, $status, $selectLimit);
    return $this->recv_getBlessVideoList();
  }

  public function send_getBlessVideoList($title, $status, array $selectLimit)
  {
    $args = new \Services\BlessVideo\BlessVideo_getBlessVideoList_args();
    $args->title = $title;
    $args->status = $status;
    $args->selectLimit = $selectLimit;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getBlessVideoList', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getBlessVideoList', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getBlessVideoList()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\Services\BlessVideo\BlessVideo_getBlessVideoList_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \Services\BlessVideo\BlessVideo_getBlessVideoList_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getBlessVideoList failed: unknown result");
  }

  public function getBlessVideoDetail($videoId)
  {
    $this->send_getBlessVideoDetail($videoId);
    return $this->recv_getBlessVideoDetail();
  }

  public function send_getBlessVideoDetail($videoId)
  {
    $args = new \Services\BlessVideo\BlessVideo_getBlessVideoDetail_args();
    $args->videoId = $videoId;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getBlessVideoDetail', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getBlessVideoDetail', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getBlessVideoDetail()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\Services\BlessVideo\BlessVideo_getBlessVideoDetail_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \Services\BlessVideo\BlessVideo_getBlessVideoDetail_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getBlessVideoDetail failed: unknown result");
  }

  public function saveBlessVideoRecord(array $data)
  {
    $this->send_saveBlessVideoRecord($data);
    return $this->recv_saveBlessVideoRecord();
  }

  public function send_saveBlessVideoRecord(array $data)
  {
    $args = new \Services\BlessVideo\BlessVideo_saveBlessVideoRecord_args();
    $args->data = $data;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'saveBlessVideoRecord', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('saveBlessVideoRecord', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_saveBlessVideoRecord()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\Services\BlessVideo\BlessVideo_saveBlessVideoRecord_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \Services\BlessVideo\BlessVideo_saveBlessVideoRecord_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("saveBlessVideoRecord failed: unknown result");
  }

  public function addPrizeQualification(array $params)
  {
    $this->send_addPrizeQualification($params);
    return $this->recv_addPrizeQualification();
  }

  public function send_addPrizeQualification(array $params)
  {
    $args = new \Services\BlessVideo\BlessVideo_addPrizeQualification_args();
    $args->params = $params;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'addPrizeQualification', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('addPrizeQualification', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_addPrizeQualification()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\Services\BlessVideo\BlessVideo_addPrizeQualification_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \Services\BlessVideo\BlessVideo_addPrizeQualification_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("addPrizeQualification failed: unknown result");
  }

}


// HELPER FUNCTIONS AND STRUCTURES

class BlessVideo_getBlessVideoList_args {
  static $isValidate = false;

  static $_TSPEC = array(
    1 => array(
      'var' => 'title',
      'isRequired' => false,
      'type' => TType::STRING,
      ),
    2 => array(
      'var' => 'status',
      'isRequired' => false,
      'type' => TType::I32,
      ),
    3 => array(
      'var' => 'selectLimit',
      'isRequired' => false,
      'type' => TType::MAP,
      'ktype' => TType::STRING,
      'vtype' => TType::STRING,
      'key' => array(
        'type' => TType::STRING,
      ),
      'val' => array(
        'type' => TType::STRING,
        ),
      ),
    );

  /**
   * @var string
   */
  public $title = null;
  /**
   * @var int
   */
  public $status = null;
  /**
   * @var array
   */
  public $selectLimit = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['title'])) {
        $this->title = $vals['title'];
      }
      if (isset($vals['status'])) {
        $this->status = $vals['status'];
      }
      if (isset($vals['selectLimit'])) {
        $this->selectLimit = $vals['selectLimit'];
      }
    }
  }

  public function getName() {
    return 'BlessVideo_getBlessVideoList_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->title);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->status);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::MAP) {
            $this->selectLimit = array();
            $_size16 = 0;
            $_ktype17 = 0;
            $_vtype18 = 0;
            $xfer += $input->readMapBegin($_ktype17, $_vtype18, $_size16);
            for ($_i20 = 0; $_i20 < $_size16; ++$_i20)
            {
              $key21 = '';
              $val22 = '';
              $xfer += $input->readString($key21);
              $xfer += $input->readString($val22);
              $this->selectLimit[$key21] = $val22;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('BlessVideo_getBlessVideoList_args');
    if ($this->title !== null) {
      $xfer += $output->writeFieldBegin('title', TType::STRING, 1);
      $xfer += $output->writeString($this->title);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->status !== null) {
      $xfer += $output->writeFieldBegin('status', TType::I32, 2);
      $xfer += $output->writeI32($this->status);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->selectLimit !== null) {
      if (!is_array($this->selectLimit)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('selectLimit', TType::MAP, 3);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->selectLimit));
        {
          foreach ($this->selectLimit as $kiter23 => $viter24)
          {
            $xfer += $output->writeString($kiter23);
            $xfer += $output->writeString($viter24);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class BlessVideo_getBlessVideoList_result {
  static $isValidate = false;

  static $_TSPEC = array(
    0 => array(
      'var' => 'success',
      'isRequired' => false,
      'type' => TType::STRUCT,
      'class' => '\Services\BlessVideo\ListResult',
      ),
    );

  /**
   * @var \Services\BlessVideo\ListResult
   */
  public $success = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'BlessVideo_getBlessVideoList_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::STRUCT) {
            $this->success = new \Services\BlessVideo\ListResult();
            $xfer += $this->success->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('BlessVideo_getBlessVideoList_result');
    if ($this->success !== null) {
      if (!is_object($this->success)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('success', TType::STRUCT, 0);
      $xfer += $this->success->write($output);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class BlessVideo_getBlessVideoDetail_args {
  static $isValidate = false;

  static $_TSPEC = array(
    1 => array(
      'var' => 'videoId',
      'isRequired' => false,
      'type' => TType::I32,
      ),
    );

  /**
   * @var int
   */
  public $videoId = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['videoId'])) {
        $this->videoId = $vals['videoId'];
      }
    }
  }

  public function getName() {
    return 'BlessVideo_getBlessVideoDetail_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->videoId);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('BlessVideo_getBlessVideoDetail_args');
    if ($this->videoId !== null) {
      $xfer += $output->writeFieldBegin('videoId', TType::I32, 1);
      $xfer += $output->writeI32($this->videoId);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class BlessVideo_getBlessVideoDetail_result {
  static $isValidate = false;

  static $_TSPEC = array(
    0 => array(
      'var' => 'success',
      'isRequired' => false,
      'type' => TType::STRUCT,
      'class' => '\Services\BlessVideo\ListResult',
      ),
    );

  /**
   * @var \Services\BlessVideo\ListResult
   */
  public $success = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'BlessVideo_getBlessVideoDetail_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::STRUCT) {
            $this->success = new \Services\BlessVideo\ListResult();
            $xfer += $this->success->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('BlessVideo_getBlessVideoDetail_result');
    if ($this->success !== null) {
      if (!is_object($this->success)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('success', TType::STRUCT, 0);
      $xfer += $this->success->write($output);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class BlessVideo_saveBlessVideoRecord_args {
  static $isValidate = false;

  static $_TSPEC = array(
    1 => array(
      'var' => 'data',
      'isRequired' => false,
      'type' => TType::MAP,
      'ktype' => TType::STRING,
      'vtype' => TType::STRING,
      'key' => array(
        'type' => TType::STRING,
      ),
      'val' => array(
        'type' => TType::STRING,
        ),
      ),
    );

  /**
   * @var array
   */
  public $data = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['data'])) {
        $this->data = $vals['data'];
      }
    }
  }

  public function getName() {
    return 'BlessVideo_saveBlessVideoRecord_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::MAP) {
            $this->data = array();
            $_size25 = 0;
            $_ktype26 = 0;
            $_vtype27 = 0;
            $xfer += $input->readMapBegin($_ktype26, $_vtype27, $_size25);
            for ($_i29 = 0; $_i29 < $_size25; ++$_i29)
            {
              $key30 = '';
              $val31 = '';
              $xfer += $input->readString($key30);
              $xfer += $input->readString($val31);
              $this->data[$key30] = $val31;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('BlessVideo_saveBlessVideoRecord_args');
    if ($this->data !== null) {
      if (!is_array($this->data)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('data', TType::MAP, 1);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->data));
        {
          foreach ($this->data as $kiter32 => $viter33)
          {
            $xfer += $output->writeString($kiter32);
            $xfer += $output->writeString($viter33);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class BlessVideo_saveBlessVideoRecord_result {
  static $isValidate = false;

  static $_TSPEC = array(
    0 => array(
      'var' => 'success',
      'isRequired' => false,
      'type' => TType::BOOL,
      ),
    );

  /**
   * @var bool
   */
  public $success = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'BlessVideo_saveBlessVideoRecord_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::BOOL) {
            $xfer += $input->readBool($this->success);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('BlessVideo_saveBlessVideoRecord_result');
    if ($this->success !== null) {
      $xfer += $output->writeFieldBegin('success', TType::BOOL, 0);
      $xfer += $output->writeBool($this->success);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class BlessVideo_addPrizeQualification_args {
  static $isValidate = false;

  static $_TSPEC = array(
    1 => array(
      'var' => 'params',
      'isRequired' => false,
      'type' => TType::MAP,
      'ktype' => TType::STRING,
      'vtype' => TType::STRING,
      'key' => array(
        'type' => TType::STRING,
      ),
      'val' => array(
        'type' => TType::STRING,
        ),
      ),
    );

  /**
   * @var array
   */
  public $params = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['params'])) {
        $this->params = $vals['params'];
      }
    }
  }

  public function getName() {
    return 'BlessVideo_addPrizeQualification_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::MAP) {
            $this->params = array();
            $_size34 = 0;
            $_ktype35 = 0;
            $_vtype36 = 0;
            $xfer += $input->readMapBegin($_ktype35, $_vtype36, $_size34);
            for ($_i38 = 0; $_i38 < $_size34; ++$_i38)
            {
              $key39 = '';
              $val40 = '';
              $xfer += $input->readString($key39);
              $xfer += $input->readString($val40);
              $this->params[$key39] = $val40;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('BlessVideo_addPrizeQualification_args');
    if ($this->params !== null) {
      if (!is_array($this->params)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('params', TType::MAP, 1);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->params));
        {
          foreach ($this->params as $kiter41 => $viter42)
          {
            $xfer += $output->writeString($kiter41);
            $xfer += $output->writeString($viter42);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class BlessVideo_addPrizeQualification_result {
  static $isValidate = false;

  static $_TSPEC = array(
    0 => array(
      'var' => 'success',
      'isRequired' => false,
      'type' => TType::MAP,
      'ktype' => TType::STRING,
      'vtype' => TType::STRING,
      'key' => array(
        'type' => TType::STRING,
      ),
      'val' => array(
        'type' => TType::STRING,
        ),
      ),
    );

  /**
   * @var array
   */
  public $success = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'BlessVideo_addPrizeQualification_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::MAP) {
            $this->success = array();
            $_size43 = 0;
            $_ktype44 = 0;
            $_vtype45 = 0;
            $xfer += $input->readMapBegin($_ktype44, $_vtype45, $_size43);
            for ($_i47 = 0; $_i47 < $_size43; ++$_i47)
            {
              $key48 = '';
              $val49 = '';
              $xfer += $input->readString($key48);
              $xfer += $input->readString($val49);
              $this->success[$key48] = $val49;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('BlessVideo_addPrizeQualification_result');
    if ($this->success !== null) {
      if (!is_array($this->success)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('success', TType::MAP, 0);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->success));
        {
          foreach ($this->success as $kiter50 => $viter51)
          {
            $xfer += $output->writeString($kiter50);
            $xfer += $output->writeString($viter51);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class BlessVideoProcessor {
  protected $handler_ = null;
  public function __construct($handler) {
    $this->handler_ = $handler;
  }

  public function process($input, $output) {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $input->readMessageBegin($fname, $mtype, $rseqid);
    $methodname = 'process_'.$fname;
    if (!method_exists($this, $methodname)) {
      $input->skip(TType::STRUCT);
      $input->readMessageEnd();
      $x = new TApplicationException('Function '.$fname.' not implemented.', TApplicationException::UNKNOWN_METHOD);
      $output->writeMessageBegin($fname, TMessageType::EXCEPTION, $rseqid);
      $x->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
      return;
    }
    $this->$methodname($rseqid, $input, $output);
    return true;
  }

  protected function process_getBlessVideoList($seqid, $input, $output) {
    $bin_accel = ($input instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary_after_message_begin');
    if ($bin_accel)
    {
      $args = thrift_protocol_read_binary_after_message_begin($input, '\Services\BlessVideo\BlessVideo_getBlessVideoList_args', $input->isStrictRead());
    }
    else
    {
      $args = new \Services\BlessVideo\BlessVideo_getBlessVideoList_args();
      $args->read($input);
      $input->readMessageEnd();
    }
    $result = new \Services\BlessVideo\BlessVideo_getBlessVideoList_result();
    $result->success = $this->handler_->getBlessVideoList($args->title, $args->status, $args->selectLimit);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($output, 'getBlessVideoList', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    }
    else
    {
      $output->writeMessageBegin('getBlessVideoList', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
  protected function process_getBlessVideoDetail($seqid, $input, $output) {
    $bin_accel = ($input instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary_after_message_begin');
    if ($bin_accel)
    {
      $args = thrift_protocol_read_binary_after_message_begin($input, '\Services\BlessVideo\BlessVideo_getBlessVideoDetail_args', $input->isStrictRead());
    }
    else
    {
      $args = new \Services\BlessVideo\BlessVideo_getBlessVideoDetail_args();
      $args->read($input);
      $input->readMessageEnd();
    }
    $result = new \Services\BlessVideo\BlessVideo_getBlessVideoDetail_result();
    $result->success = $this->handler_->getBlessVideoDetail($args->videoId);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($output, 'getBlessVideoDetail', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    }
    else
    {
      $output->writeMessageBegin('getBlessVideoDetail', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
  protected function process_saveBlessVideoRecord($seqid, $input, $output) {
    $bin_accel = ($input instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary_after_message_begin');
    if ($bin_accel)
    {
      $args = thrift_protocol_read_binary_after_message_begin($input, '\Services\BlessVideo\BlessVideo_saveBlessVideoRecord_args', $input->isStrictRead());
    }
    else
    {
      $args = new \Services\BlessVideo\BlessVideo_saveBlessVideoRecord_args();
      $args->read($input);
      $input->readMessageEnd();
    }
    $result = new \Services\BlessVideo\BlessVideo_saveBlessVideoRecord_result();
    $result->success = $this->handler_->saveBlessVideoRecord($args->data);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($output, 'saveBlessVideoRecord', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    }
    else
    {
      $output->writeMessageBegin('saveBlessVideoRecord', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
  protected function process_addPrizeQualification($seqid, $input, $output) {
    $bin_accel = ($input instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary_after_message_begin');
    if ($bin_accel)
    {
      $args = thrift_protocol_read_binary_after_message_begin($input, '\Services\BlessVideo\BlessVideo_addPrizeQualification_args', $input->isStrictRead());
    }
    else
    {
      $args = new \Services\BlessVideo\BlessVideo_addPrizeQualification_args();
      $args->read($input);
      $input->readMessageEnd();
    }
    $result = new \Services\BlessVideo\BlessVideo_addPrizeQualification_result();
    $result->success = $this->handler_->addPrizeQualification($args->params);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($output, 'addPrizeQualification', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    }
    else
    {
      $output->writeMessageBegin('addPrizeQualification', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
}

