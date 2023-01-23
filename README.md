# LanguageSystem
Viron system language for your plugin

### As a virion

If you use [Poggit](https://poggit.pmmp.io) to build your plugin, you can add it to your `.poggit.yml` like so:

```yml
projects:
  YourPlugin:
    libs:
      - src: AID-LEARNING/LanguageSystem/libLanguageSystem
        version: ^1.0.0
      - src: AID-LEARNING/libExtraPath/libExtraPathConfig
        version: ^1.0.0
        
        #mandatory if you use the command to change the language: 
      - src: jojoe77777/FormAPI/libFormAPI 
        version: ^1.3.0
      - src: Paroxity/Commando/Commando
        version: ^3.0.0
```
