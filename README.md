KayStrobach\Menu
================


The ViewHelper
--------------
Development is the name of the Menu nodes (this example is take from my dev tools)

```html
<kaystrobachmenu:widget.menu menu="Development" />
```

Define your Menu in Menus.yaml
------------------------------

Level 4 is the key defined in the viewHelper

```plain
KayStrobach:
  Menu:
    Menus:
      Development:
        Items:
          1000:
            label:      'App'
            iconclass:  'glyphicon glyphicon-chevron-left'
            url:        '/'
          1020:
            label:      ' '
            labelId:    'kaystrobach.developmenttools.home'
            iconclass:  'glyphicon glyphicon-home'
            package:    'KayStrobach.DevelopmentTools'
            action:     'index'
            controller: 'Standard'
          1023:
            label:      'Development'
            iconclass:  'glyphicon glyphicon-cog'
            section:    1
            labelId:    'development'
            package:    'KayStrobach.DevelopmentTools'
            #action: TextController
            #controller: TextController
            #package:
            #rolles:
            items:
              1230:
                label:       "Controller"
                package:     "KayStrobach.DevelopmentTools"
                controller:  "Controller"
                action:      "index"
                iconclass:   "glyphicon glyphicon-dashboard"
                labelId:     "controller"
              2240:
                label:       "Model"
                package:     "KayStrobach.DevelopmentTools"
                controller:  "Model"
                action:      "index"
                iconclass:   "glyphicon glyphicon-th"
                labelId:     "model"
              3240:
                label:       "ViewHelper"
                package:     "KayStrobach.DevelopmentTools"
                controller:  "ViewHelper"
                action:      "index"
                iconclass:   "glyphicon glyphicon-search"
                labelId:     "viewhelper"
              4240:
                label:       "Commands"
                package:     "KayStrobach.DevelopmentTools"
                controller:  "Command"
                action:      "index"
                iconclass:   "glyphicon glyphicon-wrench"
                labelId:     "commands"
              5240:
                label:       "Translations"
                package:     "KayStrobach.DevelopmentTools"
                controller:  "Translation"
                action:      "index"
                iconclass:   "glyphicon glyphicon-headphones"
                labelId:     "translations"
          1050:
            label:       "Model Entities"
            package:     "KayStrobach.DevelopmentTools"
            aggregator:  "KayStrobach\DevelopmentTools\Domain\Model\MenuItem"
            iconclass:   "glyphicon glyphicon-th"
            labelId:     "model"
            section:     1
            items:
              1:
                label: 'test'
                url: 'heise.de'
          1124:
            label:      'Documentation'
            iconclass:  'glyphicon glyphicon-book'
            section:    1
            items:
              2000:
                label:       "TYPO3 FLUID Docs"
                url:         "http://wiki.typo3.org/Fluid"
                iconclass:   "glyphicon glyphicon-book"
              2010:
                label:       "TYPO3 FLOW Quickstart"
                url:         "http://docs.typo3.org/flow/TYPO3FlowDocumentation/Quickstart/Index.html"
                iconclass:   "glyphicon glyphicon-book"
              2020:
                label:       "TYPO3 The Definitive Guide"
                url:         "http://docs.typo3.org/flow/TYPO3FlowDocumentation/TheDefinitiveGuide/Index.html"
                iconclass:   "glyphicon glyphicon-book"

#          1017:
#            label:      'Controllers'
#            iconclass:  'glyphicon glyphicon-cog'
#            section:    0
#            #aggregator: classname

```

MenuItem Provider
-----------------

possible, but currently not documented
