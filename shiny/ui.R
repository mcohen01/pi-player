library(shiny)
library(BH)

shinyUI(fluidPage(
  titlePanel("Frame Analysis"),
  sidebarLayout(
    sidebarPanel(
      fileInput('file1', 'Upload Out File',
                accept=c('text/csv',
                         'text/comma-separated-values,text/plain',
                         '.csv',
                         '.out')),
      helpText("Or submit URL of out file:"),
      textInput('url', 'URL'),
      actionButton("goButton", "Go!"),
      br(), br(),
      helpText("Mean Frame Score:"),
      verbatimTextOutput('meanScore'),
      br(), br(),      
      htmlOutput("badframes")
    ),
    mainPanel(
      conditionalPanel(
        condition = "input.url == ''", 
        plotOutput("plot")),
      conditionalPanel(
        condition = "input.url !== ''",
        plotOutput("plot2")),
      tableOutput("responses")
    )
  )
))