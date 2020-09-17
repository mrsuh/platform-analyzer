# Platform analyzer
## vc.ru / tjournal.ru / dtf.ru

# Build
```bash
sh bin/build
```

## CLI

* collect
```
php bin/console collect --help
Description:
  Collect info

Usage:
  collect [options]

Options:
      --platform=PLATFORM  vc, tjournal, dtf [default: "vc"]
      --section=SECTION    dev [default: "dev"]
```

```bash
php bin/console collect --platform=vc --section=dev -vv
```

* analyze
```bash
 php bin/console analyze --help
Description:
  Show formatted info

Usage:
  analyze [options]

Options:
      --platform=PLATFORM  vc, tjournal, dtf [default: "vc"]
      --section=SECTION    dev [default: "dev"]
      --sort=SORT          date, rating, hits, commentsCount, favoritesCount [default: "rating"]
      --limit=LIMIT         [default: -1]
      --format=FORMAT      cli, csv, md [default: "cli"]

```

```bash
php bin/console analyze --platform=vc --section=dev --format=cli --limit=10 --sort=rating
```


## Docker

* collect
```bash
docker run -v "$(pwd)/db:/app/db" mrsuh/platform-analyzer php bin/console collect --platform=vc --section=dev -vv
```

* analyze
```bash
docker run -v "$(pwd)/db:/app/db" mrsuh/platform-analyzer php bin/console analyze --platform=vc --section=dev --format=cli --limit=10 --sort=rating
```