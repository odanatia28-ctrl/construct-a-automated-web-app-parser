<?php

class AutomationParser {
    private $url;
    private $parserType;
    private $dataModel;
    private $storage;

    public function __construct($url, $parserType, $dataModel, $storage) {
        $this->url = $url;
        $this->parserType = $parserType;
        $this->dataModel = $dataModel;
        $this->storage = $storage;
    }

    public function parse() {
        $html = file_get_contents($this->url);
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $data = array();
        foreach ($this->dataModel as $field => $xpathQuery) {
            $elements = $xpath->query($xpathQuery);
            $data[$field] = array();
            foreach ($elements as $element) {
                $data[$field][] = trim($element->nodeValue);
            }
        }

        $this->storage->save($data);
    }
}

class DataModel {
    private $fields;

    public function __construct($fields) {
        $this->fields = $fields;
    }

    public function getFields() {
        return $this->fields;
    }
}

class Storage {
    private $type;

    public function __construct($type) {
        $this->type = $type;
    }

    public function save($data) {
        if ($this->type == 'file') {
            file_put_contents('output.json', json_encode($data));
        } elseif ($this->type == 'database') {
            // todo: implement database storage
        }
    }
}

$dataModel = new DataModel(array(
    'title' => '//h1',
    'description' => '//meta[@name="description"]/@content',
    'keywords' => '//meta[@name="keywords"]/@content'
));

$storage = new Storage('file');

$parser = new AutomationParser('https://example.com', 'html', $dataModel, $storage);
$parser->parse();

?>