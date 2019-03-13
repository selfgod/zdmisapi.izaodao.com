<?php
namespace Services\Exam;
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


interface ExamIf {
  /**
   * 获取试卷基本信息
   * 
   * @param int $id
   * @param array $params
   * @return array
   */
  public function getPaperInfo($id, array $params);
}


class ExamClient implements \Services\Exam\ExamIf {
  protected $input_ = null;
  protected $output_ = null;

  protected $seqid_ = 0;

  public function __construct($input, $output=null) {
    $this->input_ = $input;
    $this->output_ = $output ? $output : $input;
  }

  public function getPaperInfo($id, array $params)
  {
    $this->send_getPaperInfo($id, $params);
    return $this->recv_getPaperInfo();
  }

  public function send_getPaperInfo($id, array $params)
  {
    $args = new \Services\Exam\Exam_getPaperInfo_args();
    $args->id = $id;
    $args->params = $params;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getPaperInfo', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getPaperInfo', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getPaperInfo()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\Services\Exam\Exam_getPaperInfo_result', $this->input_->isStrictRead());
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
      $result = new \Services\Exam\Exam_getPaperInfo_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getPaperInfo failed: unknown result");
  }

}


// HELPER FUNCTIONS AND STRUCTURES

class Exam_getPaperInfo_args {
  static $isValidate = false;

  static $_TSPEC = array(
    1 => array(
      'var' => 'id',
      'isRequired' => false,
      'type' => TType::I32,
      ),
    2 => array(
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
   * @var int
   */
  public $id = null;
  /**
   * @var array
   */
  public $params = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['id'])) {
        $this->id = $vals['id'];
      }
      if (isset($vals['params'])) {
        $this->params = $vals['params'];
      }
    }
  }

  public function getName() {
    return 'Exam_getPaperInfo_args';
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
            $xfer += $input->readI32($this->id);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::MAP) {
            $this->params = array();
            $_size9 = 0;
            $_ktype10 = 0;
            $_vtype11 = 0;
            $xfer += $input->readMapBegin($_ktype10, $_vtype11, $_size9);
            for ($_i13 = 0; $_i13 < $_size9; ++$_i13)
            {
              $key14 = '';
              $val15 = '';
              $xfer += $input->readString($key14);
              $xfer += $input->readString($val15);
              $this->params[$key14] = $val15;
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
    $xfer += $output->writeStructBegin('Exam_getPaperInfo_args');
    if ($this->id !== null) {
      $xfer += $output->writeFieldBegin('id', TType::I32, 1);
      $xfer += $output->writeI32($this->id);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->params !== null) {
      if (!is_array($this->params)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('params', TType::MAP, 2);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->params));
        {
          foreach ($this->params as $kiter16 => $viter17)
          {
            $xfer += $output->writeString($kiter16);
            $xfer += $output->writeString($viter17);
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

class Exam_getPaperInfo_result {
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
    return 'Exam_getPaperInfo_result';
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
            $_size18 = 0;
            $_ktype19 = 0;
            $_vtype20 = 0;
            $xfer += $input->readMapBegin($_ktype19, $_vtype20, $_size18);
            for ($_i22 = 0; $_i22 < $_size18; ++$_i22)
            {
              $key23 = '';
              $val24 = '';
              $xfer += $input->readString($key23);
              $xfer += $input->readString($val24);
              $this->success[$key23] = $val24;
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
    $xfer += $output->writeStructBegin('Exam_getPaperInfo_result');
    if ($this->success !== null) {
      if (!is_array($this->success)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('success', TType::MAP, 0);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->success));
        {
          foreach ($this->success as $kiter25 => $viter26)
          {
            $xfer += $output->writeString($kiter25);
            $xfer += $output->writeString($viter26);
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

class ExamProcessor {
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

  protected function process_getPaperInfo($seqid, $input, $output) {
    $bin_accel = ($input instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary_after_message_begin');
    if ($bin_accel)
    {
      $args = thrift_protocol_read_binary_after_message_begin($input, '\Services\Exam\Exam_getPaperInfo_args', $input->isStrictRead());
    }
    else
    {
      $args = new \Services\Exam\Exam_getPaperInfo_args();
      $args->read($input);
      $input->readMessageEnd();
    }
    $result = new \Services\Exam\Exam_getPaperInfo_result();
    $result->success = $this->handler_->getPaperInfo($args->id, $args->params);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($output, 'getPaperInfo', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    }
    else
    {
      $output->writeMessageBegin('getPaperInfo', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
}

