library(shiny)
library(dplyr)
library(ggplot2)
library(grid)


df <- data.frame()
frame.scores <- data.frame()
url.or.file <- 0


parse.frames <- function(uri) {
  lines <- read.csv(uri, stringsAsFactors=F, header=F)
  first <- c('name', 'tutorial', 'tries', 'frame', 'correct.answer', 'student.answer')
  second <- c('is.correct', '8', '9', '10','11','12')
  names(lines) <- c(first, second)
  df <<- data.frame(lines)
  return(df)
}

filter.by <- function (filter.string) {
  correct <- tbl_df(df) %>%
    filter(is.correct == filter.string) %>%
    group_by(frame) %>%
    summarise(length(frame))
  names(correct) <- c('frame', filter.string)
  return(correct)
}

calculate.scores <- function() {
  frame.scores <<- merge(filter.by('CORRECT'),
                         filter.by('INCORRECT'),
                         all.x = T,
                         all.y = T)
  frame.scores <<- data.frame(frame.scores)
  frame.scores[is.na(frame.scores$INCORRECT), 3] <<- 0
  total <- (frame.scores$CORRECT + frame.scores$INCORRECT)
  frame.scores$score <<- frame.scores$CORRECT / total
  frame.scores$low.score <- frame.scores$score < .75
  return(frame.scores)
}

can.render <- function(input) {
  return ( (! is.null(input$file1) & length(input$file1) > 1) |
     (! is.null(input$url) & length(input$url) > 1))
}

do.plot <- function(url.or.path, code) {
  url.or.file <<- code
  parse.frames(url.or.path)
  scores <- calculate.scores()
  ggplot(scores, aes(x = frame, y = score, fill=low.score)) +
    geom_histogram(stat = "identity") +
    scale_y_continuous(expand = c(0, 0)) +
    scale_x_continuous(breaks = 1:length(frame.scores[,3])) +
    scale_fill_manual(values=c('steelblue4', 'brown2')) +
    ggtitle("Frame Analysis") +
    theme(plot.title = element_text(lineheight=.9)) +
    xlab("Frame Number") +
    ylab("Score") +
    theme(panel.grid.minor=element_blank(), panel.grid.major=element_blank()) +
    theme(legend.position = "none")
}

bad.frames <- function() {
  bad <- frame.scores[frame.scores$score < .75,c(1)]
  names(bad) <- bad
  c("Select" = 0, bad)
}

incorrect.responses <- function(frameNumber) {
  if (! is.null(frameNumber)) {
    tbl_df(df) %>%
      filter(is.correct == 'INCORRECT', frame == as.numeric(frameNumber)) %>%
      select(student.answer) %>%
      arrange(student.answer) %>%
      rename("Incorrect Responses" = student.answer)
  }
}


shinyServer(function(input, output, session) {
  
  output$plot <- renderPlot({
    if (can.render(input)) {
      if (! is.null(input$file1) & length(input$file1) > 1) {
        parse.frames(input$file1$datapath)
        do.plot(input$file1$datapath, 1)
      } 
    }
  }, width = 800, height = 400)
  
  output$plot2 <- renderPlot({
    if (input$goButton == 0)
      return()
    isolate({
      do.plot(url(input$url), 2)
    })
  }, width = 800, height = 400)
  
  output$meanScore <- renderText({
    if (can.render(input)) {
      round(mean(frame.scores$score) * 100, 2)
    }
  })
  
  output$badframes <- renderUI({
    if (can.render(input)) {
      selectInput("frameNumber", "Select Frame Number", bad.frames())
    }
  })
  
  output$responses <- renderTable({
    if (can.render(input)) {
      if (! is.null(input$frameNumber)) {
        incorrect.responses(input$frameNumber)
      }
    }
  })
  
})