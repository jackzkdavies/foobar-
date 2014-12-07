#-------------------------------------------------
#
# Project created by QtCreator 2013-10-19T19:04:42
#
#-------------------------------------------------

QT       += core gui

greaterThan(QT_MAJOR_VERSION, 4): QT += widgets

TARGET = guiAssignment
TEMPLATE = app


SOURCES += main.cpp\
        mainwindow.cpp

HEADERS  += mainwindow.h

FORMS    += mainwindow.ui

include(qextserialport/qextserialport.pri)
