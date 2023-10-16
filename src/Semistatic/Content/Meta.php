<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content;

use Dreitier\Semistatic\Content\Meta\DateValue;
use Dreitier\Semistatic\Content\Meta\MetaValue;
use Dreitier\Semistatic\Content\Meta\MetaValueFactory;
use Symfony\Component\Yaml\Yaml;

class Meta
{
    private array $configuration = [];

    public function __construct(
        public MetaValue $title,
        public MetaValue $language,
        public DateValue $date,
        public array     $slugs = [])
    {
        $this->configuration['title'] = $title;
        $this->configuration['language'] = $language;
        $this->configuration['date'] = $date;
        $this->configuration['slugs'] = $slugs;
    }

    public function title(): MetaValue
    {
        return $this->configuration['title'];
    }

    public function language(): MetaValue
    {
        return $this->configuration['language'];
    }

    public function date(): MetaValue|DateValue
    {
        return $this->valueOf('date', null, 'date');
    }

    public function slugs(): array
    {
        return $this->configuration['slugs'];
    }

    public function valueOf($pathInArray, mixed $default = null, ?string $type = null): MetaValue
    {
        // TODO cache
        return MetaValueFactory::create($this->findRaw($pathInArray), $default, $type);
    }

    public function findRaw(string $pathInArray): mixed
    {
        return static::array_get_value($this->configuration, $pathInArray);
    }

    /**
     * @see https://stackoverflow.com/a/44189105/2545275
     * @param array $array
     * @param array|string $parents
     * @param string $glue
     * @return mixed
     */
    public static function array_get_value(array &$array, $parents, $glue = '.')
    {
        if (!is_array($parents)) {
            $parents = explode($glue, $parents);
        }

        $ref = &$array;

        foreach ((array)$parents as $parent) {
            if (is_array($ref) && array_key_exists($parent, $ref)) {
                $ref = &$ref[$parent];
            } else {
                return null;
            }
        }
        return $ref;
    }

    public function merge(array $configuration)
    {
        $this->configuration = array_merge_recursive($this->configuration, $configuration);
    }

    public function configuration(): array
    {
        return $this->configuration;
    }

    public static function fromArray(array $array): Meta
    {
        $r = new Meta(
            MetaValueFactory::create($array['title'] ?? null),
            MetaValueFactory::create($array['language'] ?? null),
            MetaValueFactory::create($array['date'] ?? null, null, 'date'),
        );

        unset($array['title'], $array['language'], $array['date']);

        $r->merge($array);

        return $r;
    }

    public static function fromYaml(string $yaml): Meta
    {
        return static::fromArray(Yaml::parse($yaml));
    }

    public static function empty(): Meta
    {
        return new Meta(
            MetaValue::empty(),
            MetaValue::empty(),
            DateValue::empty()
        );
    }
}
