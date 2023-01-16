# LanguageSystem
Viron system language for your plugin

### As a virion
This library supports being included as a [virion](https://github.com/poggit/support/blob/master/virion.md).

If you use [Poggit](https://poggit.pmmp.io) to build your plugin, you can add it to your `.poggit.yml` like so:

```yml
projects:
  YourPlugin:
    libs:
      - src: AID-LEARNING/LanguageSystem/libLanguageSystem
        version: ^1.0.0
      - src: AID-LEARNING/libExtraPath/libExtraPathConfig
        version: ^1.0.0
```
