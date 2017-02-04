/********************************************************************************
** Form generated from reading UI file 'mainwindow.ui'
**
** Created: Sun 20. Oct 19:48:06 2013
**      by: Qt User Interface Compiler version 4.8.4
**
** WARNING! All changes made in this file will be lost when recompiling UI file!
********************************************************************************/

#ifndef UI_MAINWINDOW_H
#define UI_MAINWINDOW_H

#include <QtCore/QVariant>
#include <QtGui/QAction>
#include <QtGui/QApplication>
#include <QtGui/QButtonGroup>
#include <QtGui/QCheckBox>
#include <QtGui/QGridLayout>
#include <QtGui/QHeaderView>
#include <QtGui/QLabel>
#include <QtGui/QLineEdit>
#include <QtGui/QMainWindow>
#include <QtGui/QMenuBar>
#include <QtGui/QProgressBar>
#include <QtGui/QPushButton>
#include <QtGui/QSlider>
#include <QtGui/QStatusBar>
#include <QtGui/QToolBar>
#include <QtGui/QVBoxLayout>
#include <QtGui/QWidget>

QT_BEGIN_NAMESPACE

class Ui_MainWindow
{
public:
    QWidget *centralWidget;
    QSlider *ServerController;
    QCheckBox *checkBox_3;
    QCheckBox *greenLed;
    QLabel *label;
    QLabel *label_2;
    QLabel *label_3;
    QWidget *widget;
    QGridLayout *gridLayout;
    QCheckBox *StepperToggle;
    QLabel *label_4;
    QCheckBox *checkBox;
    QSlider *StepperSpeedController;
    QWidget *widget1;
    QVBoxLayout *verticalLayout;
    QCheckBox *UltSonToggle;
    QProgressBar *UtlSonMessure;
    QWidget *widget2;
    QVBoxLayout *verticalLayout_2;
    QLabel *label_5;
    QLineEdit *LCDlineEdit;
    QPushButton *pushButton;
    QMenuBar *menuBar;
    QToolBar *mainToolBar;
    QStatusBar *statusBar;

    void setupUi(QMainWindow *MainWindow)
    {
        if (MainWindow->objectName().isEmpty())
            MainWindow->setObjectName(QString::fromUtf8("MainWindow"));
        MainWindow->resize(534, 455);
        centralWidget = new QWidget(MainWindow);
        centralWidget->setObjectName(QString::fromUtf8("centralWidget"));
        ServerController = new QSlider(centralWidget);
        ServerController->setObjectName(QString::fromUtf8("ServerController"));
        ServerController->setGeometry(QRect(291, 120, 211, 19));
        ServerController->setMaximum(9);
        ServerController->setPageStep(1);
        ServerController->setValue(0);
        ServerController->setOrientation(Qt::Horizontal);
        checkBox_3 = new QCheckBox(centralWidget);
        checkBox_3->setObjectName(QString::fromUtf8("checkBox_3"));
        checkBox_3->setGeometry(QRect(290, 60, 111, 17));
        greenLed = new QCheckBox(centralWidget);
        greenLed->setObjectName(QString::fromUtf8("greenLed"));
        greenLed->setGeometry(QRect(30, 60, 121, 17));
        label = new QLabel(centralWidget);
        label->setObjectName(QString::fromUtf8("label"));
        label->setGeometry(QRect(291, 101, 50, 16));
        label_2 = new QLabel(centralWidget);
        label_2->setObjectName(QString::fromUtf8("label_2"));
        label_2->setGeometry(QRect(290, 140, 16, 16));
        label_3 = new QLabel(centralWidget);
        label_3->setObjectName(QString::fromUtf8("label_3"));
        label_3->setGeometry(QRect(480, 140, 16, 16));
        widget = new QWidget(centralWidget);
        widget->setObjectName(QString::fromUtf8("widget"));
        widget->setGeometry(QRect(30, 100, 221, 63));
        gridLayout = new QGridLayout(widget);
        gridLayout->setSpacing(6);
        gridLayout->setContentsMargins(11, 11, 11, 11);
        gridLayout->setObjectName(QString::fromUtf8("gridLayout"));
        gridLayout->setContentsMargins(0, 0, 0, 0);
        StepperToggle = new QCheckBox(widget);
        StepperToggle->setObjectName(QString::fromUtf8("StepperToggle"));

        gridLayout->addWidget(StepperToggle, 0, 0, 1, 1);

        label_4 = new QLabel(widget);
        label_4->setObjectName(QString::fromUtf8("label_4"));

        gridLayout->addWidget(label_4, 1, 2, 1, 1);

        checkBox = new QCheckBox(widget);
        checkBox->setObjectName(QString::fromUtf8("checkBox"));

        gridLayout->addWidget(checkBox, 0, 1, 1, 2);

        StepperSpeedController = new QSlider(widget);
        StepperSpeedController->setObjectName(QString::fromUtf8("StepperSpeedController"));
        StepperSpeedController->setMinimum(2);
        StepperSpeedController->setMaximum(9);
        StepperSpeedController->setPageStep(1);
        StepperSpeedController->setOrientation(Qt::Horizontal);

        gridLayout->addWidget(StepperSpeedController, 1, 0, 1, 2);

        widget1 = new QWidget(centralWidget);
        widget1->setObjectName(QString::fromUtf8("widget1"));
        widget1->setGeometry(QRect(30, 180, 221, 91));
        verticalLayout = new QVBoxLayout(widget1);
        verticalLayout->setSpacing(6);
        verticalLayout->setContentsMargins(11, 11, 11, 11);
        verticalLayout->setObjectName(QString::fromUtf8("verticalLayout"));
        verticalLayout->setContentsMargins(0, 0, 0, 0);
        UltSonToggle = new QCheckBox(widget1);
        UltSonToggle->setObjectName(QString::fromUtf8("UltSonToggle"));

        verticalLayout->addWidget(UltSonToggle);

        UtlSonMessure = new QProgressBar(widget1);
        UtlSonMessure->setObjectName(QString::fromUtf8("UtlSonMessure"));
        UtlSonMessure->setValue(24);

        verticalLayout->addWidget(UtlSonMessure);

        widget2 = new QWidget(centralWidget);
        widget2->setObjectName(QString::fromUtf8("widget2"));
        widget2->setGeometry(QRect(290, 180, 211, 91));
        verticalLayout_2 = new QVBoxLayout(widget2);
        verticalLayout_2->setSpacing(6);
        verticalLayout_2->setContentsMargins(11, 11, 11, 11);
        verticalLayout_2->setObjectName(QString::fromUtf8("verticalLayout_2"));
        verticalLayout_2->setContentsMargins(0, 0, 0, 0);
        label_5 = new QLabel(widget2);
        label_5->setObjectName(QString::fromUtf8("label_5"));

        verticalLayout_2->addWidget(label_5);

        LCDlineEdit = new QLineEdit(widget2);
        LCDlineEdit->setObjectName(QString::fromUtf8("LCDlineEdit"));

        verticalLayout_2->addWidget(LCDlineEdit);

        pushButton = new QPushButton(widget2);
        pushButton->setObjectName(QString::fromUtf8("pushButton"));

        verticalLayout_2->addWidget(pushButton);

        MainWindow->setCentralWidget(centralWidget);
        menuBar = new QMenuBar(MainWindow);
        menuBar->setObjectName(QString::fromUtf8("menuBar"));
        menuBar->setGeometry(QRect(0, 0, 534, 21));
        MainWindow->setMenuBar(menuBar);
        mainToolBar = new QToolBar(MainWindow);
        mainToolBar->setObjectName(QString::fromUtf8("mainToolBar"));
        MainWindow->addToolBar(Qt::TopToolBarArea, mainToolBar);
        statusBar = new QStatusBar(MainWindow);
        statusBar->setObjectName(QString::fromUtf8("statusBar"));
        MainWindow->setStatusBar(statusBar);

        retranslateUi(MainWindow);
        QObject::connect(pushButton, SIGNAL(clicked()), MainWindow, SLOT(pushButtonClicked()));
        QObject::connect(greenLed, SIGNAL(clicked()), MainWindow, SLOT(greenLedToggled()));
        QObject::connect(checkBox_3, SIGNAL(clicked()), MainWindow, SLOT(blueLedToggled()));
        QObject::connect(ServerController, SIGNAL(sliderMoved(int)), MainWindow, SLOT(servoDegree(int)));
        QObject::connect(StepperToggle, SIGNAL(clicked()), MainWindow, SLOT(stepperOnOff()));
        QObject::connect(checkBox, SIGNAL(clicked()), MainWindow, SLOT(stepperDir()));
        QObject::connect(StepperSpeedController, SIGNAL(actionTriggered(int)), MainWindow, SLOT(stepperSpeed(int)));

        QMetaObject::connectSlotsByName(MainWindow);
    } // setupUi

    void retranslateUi(QMainWindow *MainWindow)
    {
        MainWindow->setWindowTitle(QApplication::translate("MainWindow", "MainWindow", 0, QApplication::UnicodeUTF8));
        checkBox_3->setText(QApplication::translate("MainWindow", "Blue LED on/off", 0, QApplication::UnicodeUTF8));
        greenLed->setText(QApplication::translate("MainWindow", "Green LED on/off", 0, QApplication::UnicodeUTF8));
        label->setText(QApplication::translate("MainWindow", "Set Servo ", 0, QApplication::UnicodeUTF8));
        label_2->setText(QApplication::translate("MainWindow", "0", 0, QApplication::UnicodeUTF8));
        label_3->setText(QApplication::translate("MainWindow", "90", 0, QApplication::UnicodeUTF8));
        StepperToggle->setText(QApplication::translate("MainWindow", "Stepper motor ", 0, QApplication::UnicodeUTF8));
        label_4->setText(QApplication::translate("MainWindow", "100%", 0, QApplication::UnicodeUTF8));
        checkBox->setText(QApplication::translate("MainWindow", "Stepper Reverse", 0, QApplication::UnicodeUTF8));
        UltSonToggle->setText(QApplication::translate("MainWindow", "Ultrasonic Sensor ", 0, QApplication::UnicodeUTF8));
        label_5->setText(QApplication::translate("MainWindow", "LCD Display Message", 0, QApplication::UnicodeUTF8));
        pushButton->setText(QApplication::translate("MainWindow", "Send Text", 0, QApplication::UnicodeUTF8));
    } // retranslateUi

};

namespace Ui {
    class MainWindow: public Ui_MainWindow {};
} // namespace Ui

QT_END_NAMESPACE

#endif // UI_MAINWINDOW_H
